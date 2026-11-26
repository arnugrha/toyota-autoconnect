<?php

namespace App\Services\ServiceOrder;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceReminder;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\SparePartStockMovement;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServiceOrderService
{
    /**
     * Update status of a service order with audit logging and milestone timestamps.
     */
    public function updateStatus(ServiceOrder $order, string $newStatus, ?string $notes = null, ?User $actor = null): ServiceOrder
    {
        $validStatuses = [
            'scheduled',
            'confirmed',
            'vehicle_received',
            'inspection',
            'in_progress',
            'waiting_parts',
            'completed',
            'delivered',
            'cancelled',
        ];

        if (! in_array($newStatus, $validStatuses)) {
            throw new InvalidArgumentException("Status servis '{$newStatus}' tidak valid.");
        }

        return DB::transaction(function () use ($order, $newStatus, $notes, $actor) {
            $oldStatus = $order->status;
            $updates = ['status' => $newStatus];

            if ($newStatus === 'vehicle_received' && ! $order->received_at) {
                $updates['received_at'] = now();
            }

            if ($newStatus === 'completed' && ! $order->completed_at) {
                $updates['completed_at'] = now();
            }

            if ($newStatus === 'delivered' && ! $order->delivered_at) {
                $updates['delivered_at'] = now();
            }

            // Issue official service invoice automatically on completion or delivery
            if (in_array($newStatus, ['completed', 'delivered'])) {
                if (! $order->invoice_number) {
                    $updates['invoice_number'] = ServiceOrder::generateInvoiceNumber();
                }
                if (! $order->invoice_issued_at) {
                    $updates['invoice_issued_at'] = now();
                }
            }

            $order->update($updates);

            // Guarantee base service labor fee exists when completed/delivered
            if (in_array($newStatus, ['completed', 'delivered'])) {
                $this->ensureBaseLaborItemExists($order);
                $order->recalculateFinalCost();
            }

            // Audit Log
            $order->recordLog(
                action: 'status_transition',
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                notes: $notes ?? "Status diperbarui dari '{$order->getStatusLabelAttribute()}' ke '{$newStatus}'.",
                userId: $actor?->id
            );

            // Sync with parent ServiceBooking if completed
            if (in_array($newStatus, ['completed', 'delivered']) && $order->serviceBooking) {
                $order->serviceBooking->update(['status' => 'completed']);
            }

            // Sync after-sales reminder on completion / delivery
            if (in_array($newStatus, ['completed', 'delivered'])) {
                $this->syncAfterSalesReminder($order);
            }

            try {
                if ($order->user) {
                    $statusTitles = [
                        'in_progress' => 'Pengerjaan Servis Dimulai',
                        'waiting_parts' => 'Servis Menunggu Ketersediaan Suku Cadang',
                        'completed' => 'Servis Selesai - Kendaraan Siap Diambil',
                        'delivered' => 'Kendaraan Telah Diserahkan - Terima Kasih',
                    ];

                    $invoiceRef = $order->invoice_number ? " Invoice resmi ({$order->invoice_number}) sudah tersedia untuk Anda lihat dan cetak." : '';
                    $statusMessages = [
                        'in_progress' => "Kendaraan Toyota Anda ({$order->vehicle_name} - {$order->license_plate}) sedang dalam proses pengerjaan oleh teknisi resmi kami.",
                        'waiting_parts' => "Pengerjaan servis kendaraan Anda ({$order->vehicle_name}) sedang menunggu suku cadang resmi.",
                        'completed' => "Kabar gembira! Servis kendaraan Anda ({$order->vehicle_name} - {$order->license_plate}) telah selesai dan unit siap diambil di cabang {$order->branch->name}.{$invoiceRef}",
                        'delivered' => "Terima kasih telah melakukan servis kendaraan Anda di Toyota AutoConnect.{$invoiceRef}",
                    ];

                    if (isset($statusTitles[$newStatus])) {
                        app(NotificationService::class)->notifyUser(
                            user: $order->user,
                            title: $statusTitles[$newStatus],
                            message: $statusMessages[$newStatus],
                            category: 'service',
                            actionUrl: route('customer.service-histories.show', $order),
                            actionText: 'Lihat Detail Pengerjaan',
                            branchId: $order->branch_id,
                            metadata: ['order_id' => $order->id, 'order_number' => $order->order_number]
                        );
                    }
                }
            } catch (\Throwable $e) {
            }

            return $order->fresh();
        });
    }

    /**
     * Record vehicle inspection findings and repair recommendations.
     */
    public function recordInspection(ServiceOrder $order, array $data, ?User $actor = null): ServiceOrder
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            $updates = [
                'inspection_result' => $data['inspection_result'] ?? $order->inspection_result,
                'repair_actions' => $data['repair_actions'] ?? $order->repair_actions,
            ];

            if (isset($data['technician_name'])) {
                $updates['technician_name'] = $data['technician_name'];
            }

            if (isset($data['current_mileage'])) {
                $updates['current_mileage'] = (int) $data['current_mileage'];
            }

            $order->update($updates);

            $order->recordLog(
                action: 'inspection_recorded',
                notes: 'Hasil inspeksi teknisi dan rekomendasi perbaikan telah dicatat.',
                userId: $actor?->id
            );

            return $order->fresh();
        });
    }

    /**
     * Add a spare part or labor service item to the service order strictly from master data.
     */
    public function addItem(ServiceOrder $order, array $itemData, ?User $actor = null): ServiceOrderItem
    {
        return DB::transaction(function () use ($order, $itemData, $actor) {
            $itemType = $itemData['item_type'] ?? 'part';
            $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
            $actorId = $actor?->id ?? Auth::id();

            if ($itemType === 'part') {
                if (empty($itemData['spare_part_id'])) {
                    throw new InvalidArgumentException('Suku cadang wajib dipilih dari daftar master sparepart.');
                }

                $sparePart = SparePart::lockForUpdate()->findOrFail($itemData['spare_part_id']);

                if (! $sparePart->is_active) {
                    throw new InvalidArgumentException("Sparepart '{$sparePart->name}' sedang nonaktif dan tidak dapat digunakan.");
                }

                if ($sparePart->stock_quantity < $quantity) {
                    throw new InvalidArgumentException("Stok sparepart '{$sparePart->name}' tidak mencukupi (tersedia: {$sparePart->stock_quantity} {$sparePart->unit}, diminta: {$quantity}). Silakan lakukan pengadaan sparepart terlebih dahulu.");
                }

                $unitPrice = (float) $sparePart->selling_price;
                $subtotal = $quantity * $unitPrice;
                $requiresApproval = array_key_exists('requires_approval', $itemData)
                    ? (bool) $itemData['requires_approval']
                    : true;

                $approvalStatus = $requiresApproval ? 'pending_approval' : 'approved';
                $stockDeducted = false;

                $item = $order->items()->create([
                    'item_type' => 'part',
                    'spare_part_id' => $sparePart->id,
                    'service_type_id' => null,
                    'name' => $sparePart->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'approval_status' => $approvalStatus,
                    'approved_at' => $requiresApproval ? null : now(),
                    'stock_deducted' => false,
                ]);

                if (! $requiresApproval) {
                    $this->deductSparePartStock($sparePart, $quantity, $order, $actorId);
                    $item->update(['stock_deducted' => true]);
                    $stockDeducted = true;
                }

                $order->recalculateFinalCost();

                $statusNote = $requiresApproval
                    ? ' (Menunggu konfirmasi pelanggan)'
                    : ' (Langsung disetujui & stok gudang terpotong)';

                $order->recordLog(
                    action: 'item_added',
                    notes: "Menambahkan sparepart [{$sparePart->part_number}] {$sparePart->name} (Qty: {$quantity}, Subtotal: Rp ".number_format($subtotal, 0, ',', '.')."){$statusNote}",
                    userId: $actorId
                );

                if ($requiresApproval && $order->user) {
                    try {
                        $actionUrl = $order->serviceBooking
                            ? route('customer.service-bookings.show', $order->serviceBooking)
                            : route('customer.service-histories.show', $order);

                        app(NotificationService::class)->notifyUser(
                            user: $order->user,
                            title: 'Konfirmasi Tambahan Sparepart Servis',
                            message: "Bengkel mengusulkan tambahan suku cadang {$sparePart->name} (Qty: {$quantity}, Rp ".number_format($subtotal, 0, ',', '.').") untuk Work Order {$order->order_number}. Mohon konfirmasi persetujuan Anda.",
                            category: 'service',
                            actionUrl: $actionUrl,
                            actionText: 'Konfirmasi Sekarang',
                            branchId: $order->branch_id,
                            metadata: ['order_id' => $order->id, 'item_id' => $item->id]
                        );
                    } catch (\Throwable $e) {
                    }
                }

                return $item->load('sparePart');
            }

            if ($itemType === 'labor') {
                if (empty($itemData['service_type_id'])) {
                    throw new InvalidArgumentException('Pekerjaan jasa wajib dipilih dari daftar master layanan servis.');
                }

                $serviceType = ServiceType::findOrFail($itemData['service_type_id']);
                $unitPrice = isset($itemData['unit_price']) && $itemData['unit_price'] !== ''
                    ? max(0, (float) $itemData['unit_price'])
                    : (float) $serviceType->estimated_cost;
                $subtotal = $quantity * $unitPrice;

                $item = $order->items()->create([
                    'item_type' => 'labor',
                    'spare_part_id' => null,
                    'service_type_id' => $serviceType->id,
                    'name' => $serviceType->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'stock_deducted' => false,
                ]);

                $order->recalculateFinalCost();

                $order->recordLog(
                    action: 'item_added',
                    notes: "Menambahkan jasa servis [{$serviceType->code}] {$serviceType->name} (Qty: {$quantity}, Subtotal: Rp ".number_format($subtotal, 0, ',', '.').')',
                    userId: $actorId
                );

                return $item->load('serviceType');
            }

            throw new InvalidArgumentException("Jenis item '{$itemType}' tidak valid.");
        });
    }

    /**
     * Deduct spare part stock and write stock ledger entry.
     */
    public function deductSparePartStock(SparePart $sparePart, int $quantity, ServiceOrder $order, ?int $actorId = null): void
    {
        if ($sparePart->stock_quantity < $quantity) {
            throw new InvalidArgumentException("Stok sparepart '{$sparePart->name}' tidak mencukupi (tersedia: {$sparePart->stock_quantity} {$sparePart->unit}).");
        }

        $stockBefore = (int) $sparePart->stock_quantity;
        $stockAfter = $stockBefore - $quantity;

        $sparePart->update(['stock_quantity' => $stockAfter]);

        SparePartStockMovement::create([
            'spare_part_id' => $sparePart->id,
            'type' => 'out',
            'quantity' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => 'service_order',
            'reference_id' => $order->id,
            'created_by' => $actorId ?? Auth::id(),
            'notes' => "Pemakaian servis Work Order {$order->order_number} ({$order->vehicle_name} - {$order->license_plate})",
        ]);
    }

    /**
     * Remove an item from the service order and restore stock if already deducted.
     */
    public function removeItem(ServiceOrder $order, ServiceOrderItem $item, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $item, $actor) {
            $itemName = $item->name;
            $actorId = $actor?->id ?? Auth::id();
            $stockRestoredNote = '';

            if ($item->item_type === 'part' && $item->stock_deducted && $item->spare_part_id) {
                $sparePart = SparePart::lockForUpdate()->find($item->spare_part_id);
                if ($sparePart) {
                    $stockBefore = (int) $sparePart->stock_quantity;
                    $stockAfter = $stockBefore + $item->quantity;

                    $sparePart->update(['stock_quantity' => $stockAfter]);

                    SparePartStockMovement::create([
                        'spare_part_id' => $sparePart->id,
                        'type' => 'return',
                        'quantity' => $item->quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'service_order',
                        'reference_id' => $order->id,
                        'created_by' => $actorId,
                        'notes' => "Pengembalian stok dari penghapusan item pada Work Order {$order->order_number}",
                    ]);

                    $stockRestoredNote = " (Stok +{$item->quantity} dikembalikan ke gudang)";
                }
            }

            $item->delete();

            $order->recalculateFinalCost();

            $order->recordLog(
                action: 'item_removed',
                notes: "Menghapus item {$itemName} dari rincian servis.{$stockRestoredNote}",
                userId: $actorId
            );
        });
    }

    /**
     * Customer approves a pending additional spare part item (triggers stock deduction & cost update).
     */
    public function approveItem(ServiceOrder $order, ServiceOrderItem $item, User $customer, ?string $note = null): ServiceOrderItem
    {
        return DB::transaction(function () use ($order, $item, $customer, $note) {
            if ($item->service_order_id !== $order->id) {
                throw new InvalidArgumentException('Item tidak sesuai dengan Work Order ini.');
            }

            if (! $item->isPendingApproval()) {
                throw new InvalidArgumentException("Item '{$item->name}' sudah diproses sebelumnya.");
            }

            if ($item->item_type === 'part' && $item->spare_part_id && ! $item->stock_deducted) {
                $sparePart = SparePart::lockForUpdate()->findOrFail($item->spare_part_id);
                $this->deductSparePartStock($sparePart, $item->quantity, $order, $customer->id);
            }

            $item->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'customer_response_note' => $note,
                'stock_deducted' => $item->item_type === 'part' && (bool) $item->spare_part_id,
            ]);

            $order->recalculateFinalCost();

            $order->recordLog(
                action: 'item_approved',
                notes: "Pelanggan ({$customer->name}) MENYETUJUI tambahan sparepart {$item->name} (Qty: {$item->quantity}, {$item->formatted_subtotal}). Stok gudang otomatis terpotong.",
                userId: $customer->id
            );

            $this->notifyAdvisorAboutCustomerDecision($order, $item, true, $customer);

            return $item->fresh('sparePart');
        });
    }

    /**
     * Customer rejects a pending additional spare part item (no stock deducted, excluded from billing).
     */
    public function rejectItem(ServiceOrder $order, ServiceOrderItem $item, User $customer, ?string $reason = null): ServiceOrderItem
    {
        return DB::transaction(function () use ($order, $item, $customer, $reason) {
            if ($item->service_order_id !== $order->id) {
                throw new InvalidArgumentException('Item tidak sesuai dengan Work Order ini.');
            }

            if (! $item->isPendingApproval()) {
                throw new InvalidArgumentException("Item '{$item->name}' sudah diproses sebelumnya.");
            }

            $item->update([
                'approval_status' => 'rejected',
                'rejected_at' => now(),
                'customer_response_note' => $reason ?: 'Ditolak oleh pelanggan.',
                'stock_deducted' => false,
            ]);

            $order->recalculateFinalCost();

            $reasonSuffix = $reason ? " Alasan: {$reason}" : '';
            $order->recordLog(
                action: 'item_rejected',
                notes: "Pelanggan ({$customer->name}) MENOLAK tambahan sparepart {$item->name}.{$reasonSuffix}",
                userId: $customer->id
            );

            $this->notifyAdvisorAboutCustomerDecision($order, $item, false, $customer);

            return $item->fresh();
        });
    }

    /**
     * Customer approves all pending spare part items on a service order in one transaction.
     */
    public function approveAllPendingItems(ServiceOrder $order, User $customer): int
    {
        return DB::transaction(function () use ($order, $customer) {
            $pendingItems = $order->items()->where('approval_status', 'pending_approval')->get();

            if ($pendingItems->isEmpty()) {
                throw new InvalidArgumentException('Tidak ada item sparepart yang menunggu konfirmasi.');
            }

            foreach ($pendingItems as $item) {
                $this->approveItem($order, $item, $customer);
            }

            return $pendingItems->count();
        });
    }

    /**
     * Notify assigned service advisor when customer approves or rejects an additional part.
     */
    protected function notifyAdvisorAboutCustomerDecision(ServiceOrder $order, ServiceOrderItem $item, bool $approved, User $customer): void
    {
        try {
            if ($order->serviceAdvisor) {
                $decisionText = $approved ? 'MENYETUJUI' : 'MENOLAK';
                app(NotificationService::class)->notifyUser(
                    user: $order->serviceAdvisor,
                    title: "Konfirmasi Sparepart WO {$order->order_number}",
                    message: "Pelanggan {$customer->name} {$decisionText} tambahan sparepart {$item->name} (Qty: {$item->quantity}).",
                    category: 'service',
                    actionUrl: route('admin.service-orders.show', $order),
                    actionText: 'Buka Work Order',
                    branchId: $order->branch_id,
                    metadata: ['order_id' => $order->id, 'item_id' => $item->id, 'approved' => $approved]
                );
            }
        } catch (\Throwable $e) {
        }
    }

    /**
     * Ensure at least the base service package labor fee item exists on the work order.
     */
    public function ensureBaseLaborItemExists(ServiceOrder $order): void
    {
        $hasLabor = $order->items()
            ->whereIn('item_type', ['labor', 'service'])
            ->where('approval_status', 'approved')
            ->exists();

        if (! $hasLabor && $order->serviceType) {
            $baseCost = (float) ($order->serviceType->estimated_cost ?? 0);
            $order->items()->create([
                'item_type' => 'labor',
                'service_type_id' => $order->service_type_id,
                'name' => 'Biaya Jasa '.$order->serviceType->name,
                'quantity' => 1,
                'unit_price' => $baseCost,
                'subtotal' => $baseCost,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'stock_deducted' => false,
            ]);
        }
    }

    /**
     * Mark a service order invoice as paid by cashier/advisor.
     */
    public function markAsPaid(ServiceOrder $order, ?User $actor = null): ServiceOrder
    {
        return DB::transaction(function () use ($order, $actor) {
            $this->ensureBaseLaborItemExists($order);
            $order->recalculateFinalCost();

            $updates = [
                'payment_status' => 'paid',
                'paid_at' => now(),
            ];

            if (! $order->invoice_number) {
                $updates['invoice_number'] = ServiceOrder::generateInvoiceNumber();
            }

            if (! $order->invoice_issued_at) {
                $updates['invoice_issued_at'] = now();
            }

            $order->update($updates);
            $order->refresh();

            $order->recordLog(
                action: 'invoice_paid',
                notes: "Invoice servis {$order->invoice_number} sebesar {$order->formatted_final_cost} ditandai LUNAS.",
                userId: $actor?->id ?? Auth::id()
            );

            try {
                if ($order->user) {
                    app(NotificationService::class)->notifyUser(
                        user: $order->user,
                        title: "Pembayaran Invoice Servis {$order->invoice_number} Lunas",
                        message: "Terima kasih! Pembayaran invoice servis {$order->invoice_number} sebesar {$order->formatted_final_cost} telah kami terima dan berstatus LUNAS.",
                        category: 'service',
                        actionUrl: route('customer.service-histories.show', $order),
                        actionText: 'Cetak Invoice Lunas',
                        branchId: $order->branch_id,
                        metadata: ['order_id' => $order->id, 'invoice_number' => $order->invoice_number]
                    );
                }
            } catch (\Throwable $e) {
            }

            return $order;
        });
    }

    /**
     * Sync or create after-sales service reminder based on completed service order.
     */
    protected function syncAfterSalesReminder(ServiceOrder $order): void
    {
        $lastMileage = $order->current_mileage;
        $nextMileage = $lastMileage ? ($lastMileage + 10000) : null;
        $nextDate = now()->addMonths(6)->toDateString();

        $recommended = $nextMileage
            ? "Servis Berkala Resmi {$nextMileage} KM atau 6 Bulan"
            : 'Perawatan Berkala 6 Bulan Berikutnya';

        ServiceReminder::updateOrCreate(
            [
                'user_id' => $order->user_id,
                'license_plate' => $order->license_plate,
            ],
            [
                'vehicle_id' => $order->vehicle_id,
                'vehicle_name' => $order->vehicle_name,
                'last_service_date' => now()->toDateString(),
                'last_mileage' => $lastMileage,
                'next_service_date' => $nextDate,
                'next_service_mileage' => $nextMileage,
                'recommended_service' => $recommended,
                'status' => 'pending',
                'notes' => "Dibuat otomatis berdasarkan penyelesaian Service Order {$order->order_number}.",
            ]
        );
    }
}
