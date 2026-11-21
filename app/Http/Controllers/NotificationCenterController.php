<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    /**
     * Display the Notification Center page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->notifications();

        // Filter by category
        if ($category = $request->input('category')) {
            $query->where('data->category', $category);
        }

        // Filter by read/unread status
        if ($request->input('status') === 'unread') {
            $query->whereNull('read_at');
        } elseif ($request->input('status') === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();

        // Calculate count per category for badge counters
        $allUserNotifications = $user->notifications()->get();
        $counts = [
            'all' => $allUserNotifications->count(),
            'unread' => $allUserNotifications->whereNull('read_at')->count(),
            'vehicle_booking' => $allUserNotifications->where('data.category', 'vehicle_booking')->count(),
            'payment' => $allUserNotifications->where('data.category', 'payment')->count(),
            'test_drive' => $allUserNotifications->where('data.category', 'test_drive')->count(),
            'sales_offer' => $allUserNotifications->where('data.category', 'sales_offer')->count(),
            'service' => $allUserNotifications->where('data.category', 'service')->count(),
            'customer_service' => $allUserNotifications->where('data.category', 'customer_service')->count(),
            'account' => $allUserNotifications->where('data.category', 'account')->count(),
            'announcement' => $allUserNotifications->where('data.category', 'announcement')->count(),
        ];

        return view('notifications.index', compact('notifications', 'unreadCount', 'counts'));
    }

    /**
     * Get recent notifications and unread count for navbar dropdown.
     */
    public function dropdown(Request $request): JsonResponse
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->data['title'] ?? 'Pemberitahuan',
                    'message' => $item->data['message'] ?? '',
                    'category' => $item->data['category'] ?? 'announcement',
                    'action_url' => $item->data['action_url'] ?? null,
                    'read_at' => $item->read_at ? $item->read_at->diffForHumans() : null,
                    'created_at_human' => $item->created_at->diffForHumans(),
                    'is_unread' => is_null($item->read_at),
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $recent,
        ]);
    }

    /**
     * Mark a single notification as read and redirect to target URL if present.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca.',
            ]);
        }

        $targetUrl = $notification->data['action_url'] ?? null;
        if (!empty($targetUrl)) {
            return redirect($targetUrl);
        }

        return back()->with('success', 'Notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seluruh notifikasi berhasil ditandai sudah dibaca.',
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}

