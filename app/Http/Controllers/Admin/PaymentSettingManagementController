<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PaymentSettingManagementController extends Controller
{
    public function edit()
    {
        $methods = Schema::hasTable('payment_methods')
            ? PaymentMethod::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        $bankMethods = $methods->where('type', 'bank')->values();
        $ewalletMethods = $methods->where('type', 'ewallet')->values();
        $otherMethods = $methods->where('type', 'other')->values();
        $qrisMethod = $methods->firstWhere('type', 'qris');

        // Legacy (single row) fallback: keep available for backward compatibility / preview.
        $settings = Schema::hasTable('payment_settings')
            ? PaymentSetting::query()->first()
            : null;

        return view('dashboard.admin.payment-settings', [
            'settings' => $settings,
            'methods' => $methods,
            'bankMethods' => $bankMethods,
            'ewalletMethods' => $ewalletMethods,
            'otherMethods' => $otherMethods,
            'qrisMethod' => $qrisMethod,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Legacy endpoint: keep functional, but map to the new payment_methods table.
        // This supports old UI (single bank + QRIS) without breaking.
        $validated = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:80'],
            'bank_account_holder' => ['nullable', 'string', 'max:120'],
            'qris_image' => ['nullable', 'image', 'max:4096'],
        ]);

        if (Schema::hasTable('payment_methods')) {
            $bankName = trim((string)($validated['bank_name'] ?? ''));
            $bankAcc = trim((string)($validated['bank_account_number'] ?? ''));
            $bankHolder = trim((string)($validated['bank_account_holder'] ?? ''));

            if ($bankName !== '' || $bankAcc !== '' || $bankHolder !== '') {
                $bank = PaymentMethod::query()->where('type', 'bank')->orderBy('id')->first();
                if (!$bank) {
                    $bank = PaymentMethod::create([
                        'type' => 'bank',
                        'label' => $bankName !== '' ? $bankName : 'Transfer Bank',
                        'account_number' => $bankAcc !== '' ? $bankAcc : null,
                        'account_name' => $bankHolder !== '' ? $bankHolder : null,
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);
                } else {
                    $bank->update([
                        'label' => $bankName !== '' ? $bankName : ($bank->label ?? 'Transfer Bank'),
                        'account_number' => $bankAcc !== '' ? $bankAcc : null,
                        'account_name' => $bankHolder !== '' ? $bankHolder : null,
                        'is_active' => true,
                    ]);
                }
            }

            if ($request->hasFile('qris_image')) {
                $this->updateQris($request);
            }

            return redirect()->route('admin.settings.payment-info.edit')->with('success', 'Info pembayaran berhasil disimpan.');
        }

        // If payment_methods is not available, fallback to legacy table.
        $settings = PaymentSetting::query()->first();
        if (!$settings) {
            $settings = PaymentSetting::create([]);
        }

        $update = [
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
        ];

        if ($request->hasFile('qris_image')) {
            $this->deleteStoredQrIfLocal($settings->qris_image_path);
            $stored = $request->file('qris_image')->store('payment-settings/qris', 'public');
            $update['qris_image_path'] = 'storage/' . $stored;
        }

        $settings->update($update);

        return redirect()->route('admin.settings.payment-info.edit')->with('success', 'Info pembayaran berhasil disimpan.');
    }

    public function storeMethod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:bank,ewallet,other'],
            'label' => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_name' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        PaymentMethod::create([
            'type' => $validated['type'],
            'label' => $validated['label'],
            'account_number' => $validated['account_number'] ?? null,
            'account_name' => $validated['account_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return redirect()->route('admin.settings.payment-info.edit')->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function updateMethod(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_name' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Prevent changing type via update for safety.
        $paymentMethod->update([
            'label' => $validated['label'],
            'account_number' => $validated['account_number'] ?? null,
            'account_name' => $validated['account_name'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => array_key_exists('is_active', $validated) ? (bool)$validated['is_active'] : ($paymentMethod->is_active ?? true),
        ]);

        return redirect()->route('admin.settings.payment-info.edit')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroyMethod(PaymentMethod $paymentMethod): RedirectResponse
    {
        if (($paymentMethod->type ?? '') === 'qris') {
            $this->deleteStoredQrIfLocal($paymentMethod->image_path);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.settings.payment-info.edit')->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    public function updateQris(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qris_image' => ['required', 'image', 'max:4096'],
        ]);

        $qris = PaymentMethod::query()->where('type', 'qris')->orderBy('id')->first();
        if (!$qris) {
            $qris = PaymentMethod::create([
                'type' => 'qris',
                'label' => 'QRIS',
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        $this->deleteStoredQrIfLocal($qris->image_path);
        $stored = $request->file('qris_image')->store('payment-methods/qris', 'public');
        $qris->update([
            'image_path' => 'storage/' . $stored,
            'is_active' => true,
        ]);

        return redirect()->route('admin.settings.payment-info.edit')->with('success', 'QRIS berhasil diperbarui.');
    }

    private function deleteStoredQrIfLocal(?string $path): void
    {
        $path = (string)($path ?? '');
        if ($path === '') return;

        if (str_starts_with($path, 'storage/')) {
            $relative = substr($path, strlen('storage/'));
            Storage::disk('public')->delete($relative);
        }
    }
}
