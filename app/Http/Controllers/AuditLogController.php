<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AuditLogController extends Controller
{
    /**
     * Display a listing of system audit trail logs with filtering.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Enforce role authorization
        if (! $user->hasRole('Super Admin') && ! $user->can('audit.view')) {
            abort(403, 'Akses catatan audit trail ditolak. Fitur ini hanya dapat diakses oleh Super Administrator.');
        }

        $query = AuditLog::with('user')->latest('id');

        // Scoping for Dealer Manager if authorized
        if ($user->hasRole('Dealer Manager') && ! empty($user->branch_id)) {
            $branchUserIds = User::where('branch_id', $user->branch_id)->pluck('id');
            $query->whereIn('user_id', $branchUserIds);
        }

        // 1. Keyword search
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('description', 'like', "%{$keyword}%")
                    ->orWhere('user_name', 'like', "%{$keyword}%")
                    ->orWhere('ip_address', 'like', "%{$keyword}%")
                    ->orWhere('action', 'like', "%{$keyword}%")
                    ->orWhere('module', 'like', "%{$keyword}%");
            });
        }

        // 2. Module filter
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        // 3. Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        // 4. Role filter
        if ($request->filled('role')) {
            $query->where('user_role', $request->input('role'));
        }

        // 5. User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // 6. Date Range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        } elseif ($request->filled('start_date')) {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $query->where('created_at', '>=', $start);
        } elseif ($request->filled('end_date')) {
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        $auditLogs = $query->paginate(15)->withQueryString();

        // Dropdown metadata
        $modules = [
            'auth' => 'Autentikasi & Sesi',
            'system_settings' => 'Pengaturan Sistem',
            'branches' => 'Cabang Dealer',
            'categories' => 'Kategori Kendaraan',
            'vehicles' => 'Unit Kendaraan',
            'service_types' => 'Jenis Servis Bengkel',
            'lead_sources' => 'Sumber Lead CRM',
            'payment_methods' => 'Metode Pembayaran',
            'bookings' => 'Pemesanan SPK',
            'payments' => 'Transaksi Pembayaran',
            'service_orders' => 'Perintah Kerja (WO)',
            'crm' => 'Prospek CRM',
        ];

        $actions = [
            'login' => 'Login Berhasil',
            'login_failed' => 'Percobaan Login Gagal',
            'logout' => 'Logout',
            'password_change' => 'Perubahan Kata Sandi',
            'created' => 'Penambahan Data Baru',
            'updated' => 'Pembaruan Data',
            'deleted' => 'Penghapusan Data',
            'status_changed' => 'Perubahan Status',
            'settings_updated' => 'Perubahan Pengaturan',
        ];

        $roles = Role::pluck('name', 'name')->toArray();
        $users = User::orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.audit-logs.index', [
            'auditLogs' => $auditLogs,
            'modules' => $modules,
            'actions' => $actions,
            'roles' => $roles,
            'users' => $users,
            'filters' => [
                'q' => $request->input('q', ''),
                'module' => $request->input('module', ''),
                'action' => $request->input('action', ''),
                'role' => $request->input('role', ''),
                'user_id' => $request->input('user_id', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
            ],
        ]);
    }

    /**
     * Display detailed audit log record with value comparison.
     */
    public function show(Request $request, AuditLog $auditLog): View
    {
        $user = $request->user();

        if (! $user->hasRole('Super Admin') && ! $user->can('audit.view')) {
            abort(403, 'Akses catatan audit trail ditolak.');
        }

        // Scoping for Dealer Manager
        if ($user->hasRole('Dealer Manager') && ! empty($user->branch_id)) {
            $isBranchUser = User::where('branch_id', $user->branch_id)->where('id', $auditLog->user_id)->exists();
            if (! $isBranchUser) {
                abort(403, 'Anda tidak memiliki hak untuk melihat log aktivitas di luar cabang Anda.');
            }
        }

        return view('admin.audit-logs.show', [
            'log' => $auditLog,
        ]);
    }
}

