<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationMonitoringController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
    }

    /**
     * Display notification audit logs and delivery metrics.
     */
    public function index(Request $request): View
    {
        $query = NotificationLog::with(['user', 'branch'])->latest();

        // Branch filtering if dealer manager or requested
        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Filter by category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Filter by channel
        if ($channel = $request->input('channel')) {
            $query->where('channel', $channel);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search query
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('provider_message_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Calculate summary metrics
        $metrics = [
            'total' => NotificationLog::count(),
            'sent' => NotificationLog::whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => NotificationLog::where('status', 'failed')->count(),
            'whatsapp_count' => NotificationLog::where('channel', 'whatsapp')->count(),
            'email_count' => NotificationLog::where('channel', 'mail')->count(),
            'in_app_count' => NotificationLog::where('channel', 'in_app')->count(),
        ];

        return view('admin.notifications.monitoring', compact('logs', 'metrics'));
    }

    /**
     * Resend/retry a notification.
     */
    public function resend(Request $request, NotificationLog $log): RedirectResponse
    {
        $success = $this->notificationService->resend($log);

        if ($success) {
            return back()->with('success', "Notifikasi #{$log->id} berhasil dikirim ulang.");
        }

        return back()->with('error', "Gagal mengirim ulang notifikasi #{$log->id}. Silakan periksa log sistem.");
    }
}

