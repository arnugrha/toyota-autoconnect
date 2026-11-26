<?php

namespace App\Services\ServiceBooking;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServiceBookingService
{
    /**
     * Check if a specific time slot on a date is available at the given branch.
     *
     * @return array{available: bool, message: string, remaining_capacity: int}
     */
    public function isSlotAvailable(Branch $branch, string $date, string $time): array
    {
        $bookingDate = Carbon::parse($date)->startOfDay();

        if ($bookingDate->isPast() && ! $bookingDate->isToday()) {
            return [
                'available' => false,
                'message' => 'Tanggal servis tidak boleh berada di masa lalu.',
                'remaining_capacity' => 0,
            ];
        }

        $dayOfWeek = $bookingDate->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        $schedule = ServiceSchedule::where('branch_id', $branch->id)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $schedule || ! $schedule->is_operational) {
            return [
                'available' => false,
                'message' => 'Bengkel cabang '.$branch->name.' tidak beroperasi pada hari tersebut ('.$bookingDate->translatedFormat('l').').',
                'remaining_capacity' => 0,
            ];
        }

        // Validate time within operating hours
        $timeStr = Carbon::parse($time)->format('H:i');
        $validSlots = $schedule->getTimeSlots();

        if (! in_array($timeStr, $validSlots)) {
            return [
                'available' => false,
                'message' => 'Jam servis '.$timeStr.' berada di luar jam operasional bengkel.',
                'remaining_capacity' => 0,
            ];
        }

        // Check daily capacity
        $activeDailyCount = ServiceBooking::where('branch_id', $branch->id)
            ->whereDate('booking_date', $bookingDate)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        if ($activeDailyCount >= $schedule->max_daily_capacity) {
            return [
                'available' => false,
                'message' => 'Kapasitas kuota servis harian bengkel untuk tanggal '.$bookingDate->format('d M Y').' telah penuh.',
                'remaining_capacity' => 0,
            ];
        }

        // Check specific slot capacity
        $activeSlotCount = ServiceBooking::where('branch_id', $branch->id)
            ->whereDate('booking_date', $bookingDate)
            ->where('booking_time', $timeStr)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        if ($activeSlotCount >= $schedule->max_slot_capacity) {
            return [
                'available' => false,
                'message' => 'Slot waktu servis pukul '.$timeStr.' telah penuh dipesan oleh pelanggan lain.',
                'remaining_capacity' => 0,
            ];
        }

        $remaining = max(0, $schedule->max_slot_capacity - $activeSlotCount);

        return [
            'available' => true,
            'message' => 'Slot waktu tersedia.',
            'remaining_capacity' => $remaining,
        ];
    }

    /**
     * Get list of time slots with availability information for a date.
     *
     * @return array<int, array{time: string, available: bool, remaining_capacity: int}>
     */
    public function getAvailableSlotsForDate(Branch $branch, string $date): array
    {
        $bookingDate = Carbon::parse($date)->startOfDay();
        $dayOfWeek = $bookingDate->dayOfWeek;

        $schedule = ServiceSchedule::where('branch_id', $branch->id)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $schedule || ! $schedule->is_operational) {
            return [];
        }

        $allSlots = $schedule->getTimeSlots();
        $results = [];

        // Count existing active bookings per slot on this date
        $counts = ServiceBooking::where('branch_id', $branch->id)
            ->whereDate('booking_date', $bookingDate)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->select('booking_time', DB::raw('count(*) as total'))
            ->groupBy('booking_time')
            ->pluck('total', 'booking_time')
            ->toArray();

        // Check daily capacity
        $totalDaily = array_sum($counts);
        $dailyFull = $totalDaily >= $schedule->max_daily_capacity;

        foreach ($allSlots as $slot) {
            $bookedCount = $counts[$slot] ?? 0;
            $slotFull = $bookedCount >= $schedule->max_slot_capacity;
            $available = (! $dailyFull) && (! $slotFull);

            $results[] = [
                'time' => $slot,
                'available' => $available,
                'is_available' => $available,
                'remaining_capacity' => max(0, $schedule->max_slot_capacity - $bookedCount),
            ];
        }

        return $results;
    }

    /**
     * Create a service booking.
     */
    public function createBooking(User $customer, array $data): ServiceBooking
    {
        $booking = DB::transaction(function () use ($customer, $data) {
            // Pessimistic locking on branch to prevent concurrent race condition on quota & slot capacity
            $branch = Branch::where('id', $data['branch_id'])->lockForUpdate()->firstOrFail();
            $slotCheck = $this->isSlotAvailable($branch, $data['booking_date'], $data['booking_time']);

            if (! $slotCheck['available']) {
                throw new InvalidArgumentException($slotCheck['message']);
            }

            return ServiceBooking::create([
                'booking_code' => ServiceBooking::generateCode(),
                'user_id' => $customer->id,
                'branch_id' => $data['branch_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'vehicle_name' => $data['vehicle_name'],
                'license_plate' => strtoupper(trim($data['license_plate'])),
                'current_mileage' => ! empty($data['current_mileage']) ? (int) $data['current_mileage'] : null,
                'service_type_id' => $data['service_type_id'],
                'booking_date' => $data['booking_date'],
                'booking_time' => Carbon::parse($data['booking_time'])->format('H:i'),
                'customer_notes' => $data['customer_notes'] ?? null,
                'status' => 'pending',
            ]);
        });

        // Dispatch notifications
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyUser(
                user: $customer,
                title: 'Booking Servis Berhasil Dibuat',
                message: "Permintaan booking servis Anda (#{$booking->booking_code}) untuk unit {$booking->vehicle_name} telah kami terima dan sedang diproses oleh Service Advisor kami.",
                category: 'service',
                actionUrl: route('customer.service-bookings.show', $booking),
                actionText: 'Lihat Detail Booking',
                branchId: $booking->branch_id,
                metadata: ['booking_id' => $booking->id, 'booking_code' => $booking->booking_code]
            );

            $notificationService->notifyRole(
                roleName: 'Service Advisor',
                title: 'Booking Servis Baru Masuk',
                message: "Booking servis baru (#{$booking->booking_code}) telah diajukan oleh {$customer->name} untuk tanggal {$booking->booking_date->format('d/m/Y')} pukul {$booking->booking_time}.",
                category: 'service',
                branchId: $booking->branch_id,
                actionUrl: route('admin.service-bookings.show', $booking)
            );
        } catch (\Throwable $e) {
            // Logged inside NotificationService
        }

        return $booking;
    }

    /**
     * Confirm a service booking and automatically generate an official Service Order.
     */
    public function confirmBooking(ServiceBooking $booking, User $actor, ?string $technicianName = null): ServiceOrder
    {
        if ($booking->status === 'cancelled' || $booking->status === 'rejected') {
            throw new InvalidArgumentException("Booking servis tidak dapat dikonfirmasi karena status saat ini {$booking->status_label}.");
        }

        return DB::transaction(function () use ($booking, $actor, $technicianName) {
            $booking->update([
                'status' => 'confirmed',
                'confirmed_by' => $actor->id,
                'confirmed_at' => now(),
            ]);

            // Create or retrieve Service Order (Work Order)
            $serviceOrder = $booking->serviceOrder;

            if (! $serviceOrder) {
                $serviceOrder = ServiceOrder::create([
                    'order_number' => ServiceOrder::generateNumber(),
                    'service_booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'branch_id' => $booking->branch_id,
                    'vehicle_id' => $booking->vehicle_id,
                    'vehicle_name' => $booking->vehicle_name,
                    'license_plate' => $booking->license_plate,
                    'current_mileage' => $booking->current_mileage,
                    'service_type_id' => $booking->service_type_id,
                    'service_advisor_id' => $actor->id,
                    'technician_name' => $technicianName,
                    'status' => 'confirmed',
                    'customer_complaints' => $booking->customer_notes,
                    'estimated_cost' => $booking->serviceType->estimated_cost ?? 0,
                ]);

                // Initial standard service labor item
                if ($booking->serviceType) {
                    $laborCost = (float) ($booking->serviceType->estimated_cost ?? 0);
                    $serviceOrder->items()->create([
                        'item_type' => 'labor',
                        'service_type_id' => $booking->service_type_id,
                        'name' => 'Biaya Jasa '.$booking->serviceType->name,
                        'quantity' => 1,
                        'unit_price' => $laborCost,
                        'subtotal' => $laborCost,
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                        'stock_deducted' => false,
                    ]);

                    $serviceOrder->recalculateFinalCost();
                }

                $serviceOrder->recordLog(
                    action: 'order_created_from_booking',
                    oldStatus: null,
                    newStatus: 'confirmed',
                    notes: "Service Order diterbitkan berdasarkan konfirmasi booking {$booking->booking_code}.",
                    userId: $actor->id
                );
            }

            try {
                if ($booking->user) {
                    app(NotificationService::class)->notifyUser(
                        user: $booking->user,
                        title: 'Booking Servis Dikonfirmasi',
                        message: "Kabar baik! Booking servis (#{$booking->booking_code}) untuk kendaraan {$booking->vehicle_name} telah dikonfirmasi oleh Service Advisor bengkel resmi cabang {$booking->branch->name}.",
                        category: 'service',
                        actionUrl: route('customer.service-bookings.show', $booking),
                        actionText: 'Lihat Jadwal Servis',
                        branchId: $booking->branch_id,
                        metadata: ['booking_id' => $booking->id, 'order_id' => $serviceOrder->id]
                    );
                }
            } catch (\Throwable $e) {
            }

            return $serviceOrder;
        });
    }

    /**
     * Reschedule a service booking.
     */
    public function rescheduleBooking(ServiceBooking $booking, string $newDate, string $newTime, string $reason, User $actor): ServiceBooking
    {
        $branch = $booking->branch;
        $slotCheck = $this->isSlotAvailable($branch, $newDate, $newTime);

        if (! $slotCheck['available']) {
            throw new InvalidArgumentException($slotCheck['message']);
        }

        return DB::transaction(function () use ($booking, $newDate, $newTime, $reason) {
            $booking->update([
                'booking_date' => $newDate,
                'booking_time' => Carbon::parse($newTime)->format('H:i'),
                'status' => 'rescheduled',
                'reschedule_reason' => $reason,
            ]);

            if ($booking->serviceOrder) {
                $booking->serviceOrder->recordLog(
                    action: 'booking_rescheduled',
                    notes: "Jadwal servis diubah ke {$newDate} {$newTime}. Alasan: {$reason}."
                );
            }

            try {
                if ($booking->user) {
                    app(NotificationService::class)->notifyUser(
                        user: $booking->user,
                        title: 'Jadwal Servis Disesuaikan',
                        message: "Jadwal booking servis (#{$booking->booking_code}) telah disesuaikan menjadi {$newDate} jam {$newTime}. Catatan: {$reason}",
                        category: 'service',
                        actionUrl: route('customer.service-bookings.show', $booking),
                        actionText: 'Lihat Detail Booking',
                        branchId: $booking->branch_id
                    );
                }
            } catch (\Throwable $e) {
            }

            return $booking;
        });
    }

    /**
     * Cancel a booking by customer.
     */
    public function cancelBooking(ServiceBooking $booking, string $reason, User $actor): ServiceBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            if ($booking->serviceOrder) {
                $booking->serviceOrder->update(['status' => 'cancelled']);
                $booking->serviceOrder->recordLog(
                    action: 'order_cancelled',
                    oldStatus: $booking->serviceOrder->status,
                    newStatus: 'cancelled',
                    notes: "Booking servis dibatalkan: {$reason}"
                );
            }

            return $booking;
        });
    }

    /**
     * Reject a booking by dealer.
     */
    public function rejectBooking(ServiceBooking $booking, string $reason, User $actor): ServiceBooking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            if ($booking->serviceOrder) {
                $booking->serviceOrder->update(['status' => 'cancelled']);
            }

            try {
                if ($booking->user) {
                    app(NotificationService::class)->notifyUser(
                        user: $booking->user,
                        title: 'Pengajuan Booking Servis Ditolak',
                        message: "Mohon maaf, pengajuan booking servis (#{$booking->booking_code}) belum dapat diterima. Alasan: {$reason}",
                        category: 'service',
                        actionUrl: route('customer.service-bookings.show', $booking),
                        branchId: $booking->branch_id
                    );
                }
            } catch (\Throwable $e) {
            }

            return $booking;
        });
    }
}
