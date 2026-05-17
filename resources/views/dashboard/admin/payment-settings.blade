@extends('layouts.dashboard')

@section('title', 'Pengaturan Info Pembayaran')
@section('page_title', 'Pengaturan')

@section('content')
<div class="admin-users-page">
    <div class="admin-section-header">
        <p class="admin-section-subtitle">Kelola informasi pembayaran yang tampil di halaman Tagihan (guest).</p>
    </div>

    @if (session('success'))
        <div class="registration-detail-block admin-settings-message">
            <span class="admin-badge admin-badge-success">{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="registration-detail-block admin-settings-message">
            <span class="admin-badge admin-badge-danger">Gagal menyimpan. Periksa input.</span>
            <div class="registration-detail-divider"></div>
            @foreach ($errors->all() as $error)
                <div class="registration-detail-row"><span>Error</span><strong>{{ $error }}</strong></div>
            @endforeach
        </div>
    @endif

    <div class="registration-detail-grid">
        <div class="registration-detail-block">
            <h3>Transfer Bank (Daftar)</h3>

            @php
                $bankMethods = $bankMethods ?? collect();
                $ewalletMethods = $ewalletMethods ?? collect();
                $otherMethods = $otherMethods ?? collect();
                $qrisMethod = $qrisMethod ?? null;

                $currentRoleRaw = auth()->user()?->role ?? 'guest';
                $currentRole = $currentRoleRaw === 'super_admin' ? 'superadmin' : $currentRoleRaw;
                $canManagePaymentInfo = in_array($currentRole, ['superadmin', 'administration'], true);
            @endphp

            @if($bankMethods->count() === 0)
                <div class="registration-detail-row"><span>Data</span><strong>-</strong></div>
            @endif

            @foreach($bankMethods as $method)
                @if($canManagePaymentInfo)
                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.update', $method->id) }}" class="admin-modal-form" style="margin-top: 10px;">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="bank_label_{{ $method->id }}">Nama Bank</label>
                            <input id="bank_label_{{ $method->id }}" name="label" type="text" class="form-input" value="{{ old('label', $method->label ?? '') }}" placeholder="Contoh: BCA" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bank_acc_{{ $method->id }}">No Rekening</label>
                            <input id="bank_acc_{{ $method->id }}" name="account_number" type="text" class="form-input" value="{{ old('account_number', $method->account_number ?? '') }}" placeholder="Contoh: 1234567890" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bank_name_{{ $method->id }}">Atas Nama</label>
                            <input id="bank_name_{{ $method->id }}" name="account_name" type="text" class="form-input" value="{{ old('account_name', $method->account_name ?? '') }}" placeholder="Contoh: TK Ibnul Qoyyim" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bank_desc_{{ $method->id }}">Catatan (opsional)</label>
                            <input id="bank_desc_{{ $method->id }}" name="description" type="text" class="form-input" value="{{ old('description', $method->description ?? '') }}" placeholder="Contoh: Konfirmasi via WA setelah transfer." />
                        </div>

                        <div class="admin-action-buttons">
                            <button type="submit" class="admin-btn admin-btn-submit">Simpan</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.destroy', $method->id) }}" class="admin-inline-form" onsubmit="return confirm('Hapus metode ini?');" style="margin-top: 8px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-cancel">Hapus</button>
                    </form>
                @else
                    <div class="registration-detail-row"><span>Nama Bank</span><strong>{{ $method->label ?? '-' }}</strong></div>
                    <div class="registration-detail-row"><span>No Rekening</span><strong>{{ $method->account_number ?: '-' }}</strong></div>
                    <div class="registration-detail-row"><span>Atas Nama</span><strong>{{ $method->account_name ?: '-' }}</strong></div>
                    @if($method->description)
                        <div class="registration-detail-row"><span>Catatan</span><strong>{{ $method->description }}</strong></div>
                    @endif
                @endif

                <div class="registration-detail-divider"></div>
            @endforeach

            @if($canManagePaymentInfo)
                <h3>Tambah Bank</h3>
                <form method="POST" action="{{ route('admin.settings.payment-info.methods.store') }}" class="admin-modal-form">
                    @csrf

                    <input type="hidden" name="type" value="bank" />

                    <div class="form-group">
                        <label class="form-label" for="new_bank_label">Nama Bank</label>
                        <input id="new_bank_label" name="label" type="text" class="form-input" value="{{ old('label') }}" placeholder="Contoh: BNI" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_bank_acc">No Rekening</label>
                        <input id="new_bank_acc" name="account_number" type="text" class="form-input" value="{{ old('account_number') }}" placeholder="Contoh: 1234567890" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_bank_name">Atas Nama</label>
                        <input id="new_bank_name" name="account_name" type="text" class="form-input" value="{{ old('account_name') }}" placeholder="Contoh: TK Ibnul Qoyyim" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_bank_desc">Catatan (opsional)</label>
                        <input id="new_bank_desc" name="description" type="text" class="form-input" value="{{ old('description') }}" placeholder="Contoh: Konfirmasi via WA setelah transfer." />
                    </div>

                    <button type="submit" class="admin-btn admin-btn-add">Tambah</button>
                </form>
            @endif

            <div class="registration-detail-divider"></div>

            <h3>E-Wallet (Daftar)</h3>
            @if($ewalletMethods->count() === 0)
                <div class="registration-detail-row"><span>Data</span><strong>-</strong></div>
            @endif

            @foreach($ewalletMethods as $method)
                @if($canManagePaymentInfo)
                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.update', $method->id) }}" class="admin-modal-form" style="margin-top: 10px;">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="ew_label_{{ $method->id }}">Provider</label>
                            <input id="ew_label_{{ $method->id }}" name="label" type="text" class="form-input" value="{{ old('label', $method->label ?? '') }}" placeholder="Contoh: DANA" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="ew_acc_{{ $method->id }}">Nomor / ID</label>
                            <input id="ew_acc_{{ $method->id }}" name="account_number" type="text" class="form-input" value="{{ old('account_number', $method->account_number ?? '') }}" placeholder="Contoh: 081234567890" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="ew_name_{{ $method->id }}">Atas Nama (opsional)</label>
                            <input id="ew_name_{{ $method->id }}" name="account_name" type="text" class="form-input" value="{{ old('account_name', $method->account_name ?? '') }}" placeholder="Contoh: TK Ibnul Qoyyim" />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="ew_desc_{{ $method->id }}">Catatan (opsional)</label>
                            <input id="ew_desc_{{ $method->id }}" name="description" type="text" class="form-input" value="{{ old('description', $method->description ?? '') }}" placeholder="Contoh: Tulis nama siswa di catatan." />
                        </div>

                        <div class="admin-action-buttons">
                            <button type="submit" class="admin-btn admin-btn-submit">Simpan</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.destroy', $method->id) }}" class="admin-inline-form" onsubmit="return confirm('Hapus metode ini?');" style="margin-top: 8px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-cancel">Hapus</button>
                    </form>
                @else
                    <div class="registration-detail-row"><span>Provider</span><strong>{{ $method->label ?? '-' }}</strong></div>
                    <div class="registration-detail-row"><span>Nomor / ID</span><strong>{{ $method->account_number ?: '-' }}</strong></div>
                    @if($method->account_name)
                        <div class="registration-detail-row"><span>Atas Nama</span><strong>{{ $method->account_name }}</strong></div>
                    @endif
                    @if($method->description)
                        <div class="registration-detail-row"><span>Catatan</span><strong>{{ $method->description }}</strong></div>
                    @endif
                @endif

                <div class="registration-detail-divider"></div>
            @endforeach

            @if($canManagePaymentInfo)
                <h3>Tambah E-Wallet</h3>
                <form method="POST" action="{{ route('admin.settings.payment-info.methods.store') }}" class="admin-modal-form">
                    @csrf

                    <input type="hidden" name="type" value="ewallet" />

                    <div class="form-group">
                        <label class="form-label" for="new_ew_label">Provider</label>
                        <input id="new_ew_label" name="label" type="text" class="form-input" value="{{ old('label') }}" placeholder="Contoh: OVO" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_ew_acc">Nomor / ID</label>
                        <input id="new_ew_acc" name="account_number" type="text" class="form-input" value="{{ old('account_number') }}" placeholder="Contoh: 081234567890" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_ew_name">Atas Nama (opsional)</label>
                        <input id="new_ew_name" name="account_name" type="text" class="form-input" value="{{ old('account_name') }}" placeholder="Contoh: TK Ibnul Qoyyim" />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_ew_desc">Catatan (opsional)</label>
                        <input id="new_ew_desc" name="description" type="text" class="form-input" value="{{ old('description') }}" placeholder="Contoh: Tulis nama siswa di catatan." />
                    </div>

                    <button type="submit" class="admin-btn admin-btn-add">Tambah</button>
                </form>
            @endif

            <div class="registration-detail-divider"></div>

            <h3>Lainnya (Daftar)</h3>
            @if($otherMethods->count() === 0)
                <div class="registration-detail-row"><span>Data</span><strong>-</strong></div>
            @endif

            @foreach($otherMethods as $method)
                @if($canManagePaymentInfo)
                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.update', $method->id) }}" class="admin-modal-form" style="margin-top: 10px;">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="form-label" for="other_label_{{ $method->id }}">Nama Metode</label>
                            <input id="other_label_{{ $method->id }}" name="label" type="text" class="form-input" value="{{ old('label', $method->label ?? '') }}" placeholder="Contoh: Transfer Manual" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="other_desc_{{ $method->id }}">Keterangan</label>
                            <input id="other_desc_{{ $method->id }}" name="description" type="text" class="form-input" value="{{ old('description', $method->description ?? '') }}" placeholder="Contoh: Hubungi admin untuk detail." />
                        </div>

                        <div class="admin-action-buttons">
                            <button type="submit" class="admin-btn admin-btn-submit">Simpan</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.settings.payment-info.methods.destroy', $method->id) }}" class="admin-inline-form" onsubmit="return confirm('Hapus metode ini?');" style="margin-top: 8px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn admin-btn-cancel">Hapus</button>
                    </form>
                @else
                    <div class="registration-detail-row"><span>Metode</span><strong>{{ $method->label ?? '-' }}</strong></div>
                    <div class="registration-detail-row"><span>Keterangan</span><strong>{{ $method->description ?: '-' }}</strong></div>
                @endif

                <div class="registration-detail-divider"></div>
            @endforeach

            @if($canManagePaymentInfo)
                <h3>Tambah Metode Lainnya</h3>
                <form method="POST" action="{{ route('admin.settings.payment-info.methods.store') }}" class="admin-modal-form">
                    @csrf

                    <input type="hidden" name="type" value="other" />

                    <div class="form-group">
                        <label class="form-label" for="new_other_label">Nama Metode</label>
                        <input id="new_other_label" name="label" type="text" class="form-input" value="{{ old('label') }}" placeholder="Contoh: Bayar Tunai" required />
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_other_desc">Keterangan</label>
                        <input id="new_other_desc" name="description" type="text" class="form-input" value="{{ old('description') }}" placeholder="Contoh: Datang ke sekolah pada jam kerja." />
                    </div>

                    <button type="submit" class="admin-btn admin-btn-add">Tambah</button>
                </form>
            @endif

            <div class="registration-detail-divider"></div>

            @if($canManagePaymentInfo)
                <h3>QRIS</h3>
                <form method="POST" action="{{ route('admin.settings.payment-info.qris.update') }}" enctype="multipart/form-data" class="admin-modal-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label" for="qris_image">Gambar QRIS</label>
                        <input id="qris_image" name="qris_image" type="file" class="form-input" accept="image/*" />
                        <div class="form-label-hint">Upload gambar baru untuk mengganti QRIS.</div>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-submit">Simpan QRIS</button>
                </form>
            @endif
        </div>

        <div class="registration-detail-block">
            <h3>Preview</h3>
            <div class="registration-detail-row"><span>Transfer Bank</span><strong>{{ ($bankMethods->count() ?? 0) > 0 ? $bankMethods->count().' item' : '-' }}</strong></div>
            <div class="registration-detail-row"><span>E-Wallet</span><strong>{{ ($ewalletMethods->count() ?? 0) > 0 ? $ewalletMethods->count().' item' : '-' }}</strong></div>
            <div class="registration-detail-row"><span>Lainnya</span><strong>{{ ($otherMethods->count() ?? 0) > 0 ? $otherMethods->count().' item' : '-' }}</strong></div>

            <div class="registration-detail-divider"></div>

            <h3>Transfer Bank</h3>
            @if(($bankMethods->count() ?? 0) === 0)
                <div class="registration-detail-row"><span>Bank</span><strong>-</strong></div>
            @endif
            @foreach($bankMethods as $method)
                <div class="registration-detail-row"><span>Bank</span><strong>{{ $method->label ?? '-' }}</strong></div>
                <div class="registration-detail-row"><span>No Rekening</span><strong>{{ $method->account_number ?: '-' }}</strong></div>
                <div class="registration-detail-row"><span>Atas Nama</span><strong>{{ $method->account_name ?: '-' }}</strong></div>
                @if($method->description)
                    <div class="registration-detail-row"><span>Catatan</span><strong>{{ $method->description }}</strong></div>
                @endif
                <div class="registration-detail-divider"></div>
            @endforeach

            <h3>E-Wallet</h3>
            @if(($ewalletMethods->count() ?? 0) === 0)
                <div class="registration-detail-row"><span>Metode</span><strong>-</strong></div>
            @endif
            @foreach($ewalletMethods as $method)
                <div class="registration-detail-row"><span>Provider</span><strong>{{ $method->label ?? '-' }}</strong></div>
                <div class="registration-detail-row"><span>Nomor / ID</span><strong>{{ $method->account_number ?: '-' }}</strong></div>
                @if($method->account_name)
                    <div class="registration-detail-row"><span>Atas Nama</span><strong>{{ $method->account_name }}</strong></div>
                @endif
                @if($method->description)
                    <div class="registration-detail-row"><span>Catatan</span><strong>{{ $method->description }}</strong></div>
                @endif
                <div class="registration-detail-divider"></div>
            @endforeach

            <h3>Lainnya</h3>
            @if(($otherMethods->count() ?? 0) === 0)
                <div class="registration-detail-row"><span>Metode</span><strong>-</strong></div>
            @endif
            @foreach($otherMethods as $method)
                <div class="registration-detail-row"><span>Metode</span><strong>{{ $method->label ?? '-' }}</strong></div>
                <div class="registration-detail-row"><span>Keterangan</span><strong>{{ $method->description ?: '-' }}</strong></div>
            @endforeach

            <div class="registration-detail-divider"></div>

            <h3>QRIS</h3>
            @php
                $qrisPath = (string)($qrisMethod?->image_path ?? '');
                if ($qrisPath === '' && ($settings?->qris_image_path ?? null)) {
                    $qrisPath = (string)($settings->qris_image_path ?? '');
                }
            @endphp

            @if($qrisPath)
                <div class="registration-detail-row"><span>QRIS</span><strong><a href="{{ asset($qrisPath) }}" target="_blank" rel="noopener">Lihat</a></strong></div>
                <div class="registration-detail-divider"></div>
                <img src="{{ asset($qrisPath) }}" alt="QRIS" class="admin-settings-qris-image" />
            @else
                <div class="registration-detail-row"><span>QRIS</span><strong>-</strong></div>
            @endif
        </div>
    </div>
</div>
@endsection
