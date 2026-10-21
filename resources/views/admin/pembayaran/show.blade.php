<x-layouts.app title="Detail Pembayaran">

    <div class="space-y-6">

        {{-- Header --}}
        <div>
            <a href="{{ route('admin.pembayaran.index') }}"
               class="btn btn-ghost btn-sm mb-3">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>

            <h1 class="text-2xl font-bold text-slate-800">
                Detail Pembayaran #{{ $pembayaran->id }}
            </h1>

            <p class="text-slate-500 mt-1">
                Periksa bukti pembayaran sebelum melakukan verifikasi.
            </p>
        </div>

        @php
            $periksa = $pembayaran->periksa;
            $pendaftaran = $periksa->daftarPoli;
            $pasien = $pendaftaran->pasien;
            $dokter = $pendaftaran->jadwalPeriksa->dokter;
        @endphp

        {{-- Payment Information --}}
        <div class="card bg-base-100 shadow-md border border-slate-200">
            <div class="card-body space-y-4">

                <h2 class="card-title">Informasi Pembayaran</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <p class="text-sm text-slate-500">Pasien</p>
                        <p class="font-semibold">{{ $pasien->nama }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Dokter</p>
                        <p class="font-semibold">{{ $dokter->nama }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Total Tagihan</p>
                        <p class="font-bold text-lg">
                            Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Status Pembayaran</p>

                        @if($pembayaran->status === 'menunggu')
                            <span class="badge badge-warning">Menunggu</span>
                        @elseif($pembayaran->status === 'lunas')
                            <span class="badge badge-success text-white">Lunas</span>
                        @else
                            <span class="badge badge-error text-white">Ditolak</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Tanggal Upload</p>
                        <p>{{ $pembayaran->tanggal_upload?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Tanggal Verifikasi</p>
                        <p>{{ $pembayaran->tanggal_verifikasi?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Payment Proof --}}
        <div class="card bg-base-100 shadow-md border border-slate-200">
            <div class="card-body">

                <h2 class="card-title mb-4">Bukti Pembayaran</h2>

                @if($pembayaran->bukti_pembayaran)
                    <img
                        src="{{ asset('storage/' . $pembayaran->bukti_pembayaran) }}"
                        alt="Bukti pembayaran pasien"
                        class="max-w-md w-full rounded-xl border border-slate-200"
                    >
                @else
                    <p class="text-slate-500">
                        Belum ada bukti pembayaran.
                    </p>
                @endif

            </div>
        </div>

        {{-- Verification --}}
        @if($pembayaran->status === 'menunggu')
            <div class="card bg-base-100 shadow-md border border-slate-200">
                <div class="card-body">

                    <h2 class="card-title">Verifikasi Pembayaran</h2>

                    <p class="text-slate-500">
                        Pastikan bukti pembayaran sudah diperiksa.
                    </p>

                    @if($errors->any())
                        <div class="alert alert-error mt-3">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3 mt-4">

                        <form
                            action="{{ route('admin.pembayaran.verify', $pembayaran->id) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menyatakan pembayaran ini lunas?')"
                        >
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="lunas">

                            <button type="submit"
                                    class="btn"
                                    style="background-color: #16a34a !important;
                                        color: #ffffff !important;
                                        border-color: #16a34a !important;">
                                <i class="fas fa-check-circle"></i>
                                Verifikasi Lunas
                            </button>
                        </form>

                        <form
                            action="{{ route('admin.pembayaran.verify', $pembayaran->id) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menolak pembayaran ini?')"
                        >
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="ditolak">

                            <button type="submit"
                                    class="btn"
                                    style="background-color: #dc2626 !important;
                                        color: #ffffff !important;
                                        border-color: #dc2626 !important;">
                                <i class="fas fa-times-circle"></i>
                                Tolak Pembayaran
                            </button>
                        </form>

                    </div>

                </div>
            </div>
        @endif

    </div>

</x-layouts.app>