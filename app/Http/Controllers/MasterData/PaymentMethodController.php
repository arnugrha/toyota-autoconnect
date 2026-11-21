<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index(Request $request): View
    {
        $query = PaymentMethod::orderBy('sort_order')->orderBy('id');

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $paymentMethods = $query->paginate(10)->withQueryString();

        $categories = [
            'virtual_account' => 'Virtual Account Bank',
            'e_wallet' => 'e-Wallet & QRIS Digital',
            'card' => 'Kartu Kredit / Debit Online',
            'offline' => 'Pelunasan Tunai di Dealer',
        ];

        return view('admin.master-data.payment-methods.index', [
            'paymentMethods' => $paymentMethods,
            'categories' => $categories,
            'filters' => [
                'q' => $request->input('q', ''),
                'category' => $request->input('category', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('payment_methods', 'code')],
            'category' => ['required', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $method = PaymentMethod::create($validated);

        app(AuditTrailService::class)->logCreated($method, 'payment_methods', "Penambahan metode pembayaran baru: {$method->name} ({$method->code})");

        return back()->with('success', "Metode pembayaran '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id)],
            'category' => ['required', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $paymentMethod->getAttributes();
        $paymentMethod->update($validated);

        app(AuditTrailService::class)->logUpdated($paymentMethod, 'payment_methods', $oldValues, $paymentMethod->getAttributes(), "Pembaruan informasi metode pembayaran: {$paymentMethod->name}");

        return back()->with('success', "Metode pembayaran '{$paymentMethod->name}' berhasil diperbarui.");
    }

    public function toggleStatus(PaymentMethod $paymentMethod): RedirectResponse
    {
        $oldStatus = $paymentMethod->is_active ? 'active' : 'inactive';
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);
        $newStatus = $paymentMethod->is_active ? 'active' : 'inactive';
        $statusText = $paymentMethod->is_active ? 'diaktifkan' : 'dinonaktifkan';

        app(AuditTrailService::class)->logStatusChanged($paymentMethod, 'payment_methods', $oldStatus, $newStatus, "Perubahan status operasional metode pembayaran.");

        return back()->with('success', "Status metode pembayaran '{$paymentMethod->name}' berhasil {$statusText}.");
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        // Enforce referential integrity: do not delete if used in payments
        $hasPayments = Payment::where('payment_method', $paymentMethod->code)->exists();

        if ($hasPayments) {
            return back()->with('error', "Metode pembayaran '{$paymentMethod->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi pembayaran. Anda disarankan menonaktifkan status metode pembayaran.");
        }

        $name = $paymentMethod->name;
        app(AuditTrailService::class)->logDeleted($paymentMethod, 'payment_methods', "Penghapusan opsi metode pembayaran: {$name}");
        $paymentMethod->delete();

        return back()->with('success', "Metode pembayaran '{$name}' berhasil dihapus.");
    }
}

