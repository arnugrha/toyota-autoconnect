<?php

namespace App\Console\Commands;

use App\Models\ServiceReminder;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendServiceRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toyota:send-service-reminders {--days=14 : Jumlah hari menjelang jatuh tempo servis}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirimkan notifikasi pengingat perawatan berkala (service reminder) kepada pelanggan yang jatuh tempo.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->toDateString();

        $this->info("Memeriksa pengingat servis yang jatuh tempo hingga {$targetDate}...");

        $reminders = ServiceReminder::where('status', 'pending')
            ->whereDate('next_service_date', '<=', $targetDate)
            ->with(['user', 'vehicle'])
            ->get();

        $notificationService = app(NotificationService::class);
        $count = 0;
        foreach ($reminders as $reminder) {
            $reminder->update([
                'status' => 'sent',
                'reminder_sent_at' => now(),
            ]);

            if ($reminder->user) {
                $notificationService->notifyUser(
                    user: $reminder->user,
                    title: 'Pengingat Servis Berkala Toyota Anda',
                    message: "Halo {$reminder->user->name}, kendaraan Anda {$reminder->vehicle_name} ({$reminder->license_plate}) mendekati estimasi jadwal servis berkala pada tanggal {$reminder->next_service_date->format('d M Y')}. Segera jadwalkan servis berkala Anda untuk menjaga garansi dan kenyamanan berkendara.",
                    category: 'service',
                    actionUrl: route('customer.service-bookings.create'),
                    actionText: 'Booking Servis Sekarang',
                    metadata: ['reminder_id' => $reminder->id, 'license_plate' => $reminder->license_plate],
                    isTransactional: false
                );
            }

            $this->line("Pengingat dikirim ke: {$reminder->user->name} ({$reminder->vehicle_name} - {$reminder->license_plate}) untuk tanggal {$reminder->next_service_date->format('d M Y')}.");
            $count++;
        }

        $this->info("Berhasil mengirim {$count} pengingat servis pelanggan.");

        return Command::SUCCESS;
    }
}

