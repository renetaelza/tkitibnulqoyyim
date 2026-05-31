@php
    $studentName = $studentPayment->student?->name ?? 'Murid';
    $paymentName = $studentPayment->payment?->name ?? 'Pembayaran';
    $periodLabel = $studentPayment->payment_period ?? '-';
    $totalLabel = 'Rp ' . number_format((float)($studentPayment->total_amount ?? 0), 0, ',', '.');
    $discountLabel = 'Rp ' . number_format((float)($studentPayment->discount_amount ?? 0), 0, ',', '.');
    $finalLabel = 'Rp ' . number_format((float)($studentPayment->final_amount ?? 0), 0, ',', '.');
    $statusLabel = strtoupper((string)($studentPayment->status ?? 'pending'));
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tagihan baru</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family: Arial, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb; padding:24px 12px;">
        <tr>2
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(15, 23, 42, 0.08);">
                    <tr>
                        <td style="padding:24px 28px; background:#0f172a; color:#ffffff;">
                            <h1 style="margin:0; font-size:20px;">Tagihan Baru Telah Dibuat</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <p style="margin:0 0 12px;">Halo {{ $recipientUser?->name ?? 'Orang Tua/Wali' }},</p>
                            <p style="margin:0 0 16px;">Tagihan baru untuk <strong>{{ $studentName }}</strong> telah dibuat. Berikut rincian singkatnya:</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb; border-radius:10px;">
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;">Jenis Pembayaran</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;" align="right"><strong>{{ $paymentName }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;">Periode</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;" align="right">{{ $periodLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;">Total</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;" align="right">{{ $totalLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;">Diskon</td>
                                    <td style="padding:12px 14px; border-bottom:1px solid #e5e7eb;" align="right">{{ $discountLabel }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;">Final</td>
                                    <td style="padding:12px 14px;" align="right"><strong>{{ $finalLabel }}</strong></td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0;">Status saat ini: <strong>{{ $statusLabel }}</strong></p>

                            <div style="margin:20px 0 0;">
                                <a href="{{ $billUrl }}" style="display:inline-block; padding:10px 18px; background:#0ea5e9; color:#ffffff; text-decoration:none; border-radius:8px;">Lihat Tagihan</a>
                            </div>

                            <p style="margin:18px 0 0; color:#6b7280; font-size:13px;">Jika link tidak terbuka, buka dashboard lalu pilih menu Tagihan.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px; background:#f8fafc; color:#6b7280; font-size:12px;">
                            Email ini dikirim otomatis oleh {{ config('app.name') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
