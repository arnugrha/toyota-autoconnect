<?php

namespace App\Services\SparePart;

use App\Models\SparePart;
use App\Models\SparePartProcurement;
use App\Models\SparePartStockMovement;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SparePartProcurementService
{
    /**
     * Create a new spare part procurement and automatically increment stock quantities.
     *
     * @param  array{procurement_date: string, supplier_name: string, branch_id?: int|null, notes?: string|null}  $headerData
     * @param  array<int, array{spare_part_id: int, quantity: int, unit_cost: float|int}>  $items
     */
    public function createProcurement(array $headerData, array $items, User $actor): SparePartProcurement
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Minimal harus ada 1 item sparepart dalam pengadaan.');
        }

        return DB::transaction(function () use ($headerData, $items, $actor) {
            $procurement = SparePartProcurement::create([
                'procurement_number' => SparePartProcurement::generateNumber(),
                'procurement_date' => $headerData['procurement_date'],
                'supplier_name' => trim($headerData['supplier_name']),
                'branch_id' => $headerData['branch_id'] ?? null,
                'created_by' => $actor->id,
                'status' => 'received',
                'total_cost' => 0,
                'notes' => $headerData['notes'] ?? null,
            ]);

            $grandTotal = 0.0;
            $totalUnits = 0;

            foreach ($items as $itemData) {
                $sparePart = SparePart::lockForUpdate()->findOrFail($itemData['spare_part_id']);
                $quantity = max(1, (int) $itemData['quantity']);
                $unitCost = max(0, (float) $itemData['unit_cost']);
                $subtotal = $quantity * $unitCost;

                $procurement->items()->create([
                    'spare_part_id' => $sparePart->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $stockBefore = (int) $sparePart->stock_quantity;
                $stockAfter = $stockBefore + $quantity;

                $updatePayload = ['stock_quantity' => $stockAfter];
                if ($unitCost > 0) {
                    $updatePayload['purchase_price'] = $unitCost;
                }
                $sparePart->update($updatePayload);

                SparePartStockMovement::create([
                    'spare_part_id' => $sparePart->id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'procurement',
                    'reference_id' => $procurement->id,
                    'created_by' => $actor->id,
                    'notes' => "Pengadaan {$procurement->procurement_number} dari {$procurement->supplier_name}",
                ]);

                $grandTotal += $subtotal;
                $totalUnits += $quantity;
            }

            $procurement->update(['total_cost' => $grandTotal]);

            app(AuditTrailService::class)->logCreated(
                $procurement,
                'spare_part_procurements',
                "Pengadaan sparepart {$procurement->procurement_number} dari {$procurement->supplier_name} ({$totalUnits} unit, total Rp ".number_format($grandTotal, 0, ',', '.').'). Stok otomatis bertambah.',
                $actor
            );

            return $procurement->load(['items.sparePart', 'branch', 'creator']);
        });
    }
}
