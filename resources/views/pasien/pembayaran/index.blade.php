<x-layouts.app title="Pembayaran">

    {{-- Page Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Pembayaran
        </h2>
        <p class="text-slate-500 mt-1">
            Lihat tagihan pemeriksaan dan unggah bukti pembayaran Anda.
        </p>
    </div>

    {{-- Success Message --}}

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Payment List --}}
    <div class="space-y-5">

        @forelse($tagihans as $tagihan)
            @php
                $pembayaran = $tagihan->pembayaran;
                $dokter = $tagihan->daftarPoli?->jadwalPeriksa?->dokter;
                $status = $pembayaran?->status ?? 'belum_bayar';
                $canUpload = in_array($status, ['belum_bayar', 'ditolak']);
            @endphp

            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body">

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-bold text-lg text-slate-800">
                            Pemeriksaan #{{ $tagihan->id }}
                        </h3>

                        @if($status === 'lunas')
                            <span class="badge badge-success text-white">Lunas</span>
                        @elseif($status === 'menunggu')
                            <span class="badge badge-warning">Menunggu Verifikasi</span>
                        @elseif($status === 'ditolak')
                            <span class="badge badge-error text-white">Ditolak</span>
                        @else
                            <span class="badge badge-ghost">Belum Bayar</span>
                        @endif
                    </div>

                    {{-- Examination Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

                        <div>
                            <p class="text-sm text-slate-500">Tanggal Periksa</p>
                            <p class="font-semibold">
                                {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->format('d/m/Y') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Dokter</p>
                            <p class="font-semibold">
                                {{ $dokter?->nama ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Total Tagihan</p>
                            <p class="text-xl font-bold text-blue-700">
                                Rp {{ number_format($tagihan->biaya_periksa ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Tanggal Upload</p>
                            <p class="font-semibold">
                                {{ $pembayaran?->tanggal_upload?->format('d/m/Y H:i') ?? '-' }}
                            </p>
                        </div>

                    </div>

                    {{-- Upload Payment Proof --}}
                    @if($canUpload)
                        <div class="border-t border-slate-200 mt-5 pt-5">

                            @if($status === 'ditolak')
                                <p class="text-sm text-red-600 mb-3">
                                    Bukti pembayaran sebelumnya ditolak.
                                    Silakan unggah bukti pembayaran yang baru.
                                </p>
                            @endif

                            <form
                                action="{{ route('pasien.pembayaran.upload', $tagihan->id) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="space-y-3"
                            >
                                @csrf

                                <label class="block text-sm font-semibold text-slate-700">
                                    Upload Bukti Pembayaran
                                </label>

                                <input
                                    type="file"
                                    name="bukti_pembayaran"
                                    accept=".jpg,.jpeg,.png"
                                    class="file-input file-input-bordered w-full max-w-md"
                                    required
                                >

                                <p class="text-xs text-slate-500">
                                    Format JPG, JPEG, atau PNG. Maksimal 2 MB.
                                </p>

                                <div>
                                    <button
                                        type="submit"
                                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg"
                                    >
                                        <i class="fas fa-upload"></i>
                                        Upload Bukti Pembayaran
                                    </button>
                                </div>

                            </form>

                        </div>
                    @elseif($status === 'menunggu')
                        <div class="mt-5 p-4 bg-amber-50 rounded-xl text-amber-800 text-sm">
                            <i class="fas fa-clock mr-1"></i>
                            Bukti pembayaran Anda sedang menunggu verifikasi Admin.
                        </div>
                    @elseif($status === 'lunas')
                        <div class="mt-5 p-4 bg-emerald-50 rounded-xl text-emerald-800 text-sm">
                            <i class="fas fa-check-circle mr-1"></i>
                            Pembayaran Anda sudah diverifikasi dan dinyatakan lunas.
                        </div>
                    @endif

                </div>
            </div>

        @empty
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body items-center text-center py-14">
                    <i class="fas fa-file-invoice text-4xl text-slate-400 mb-3"></i>
                    <p class="text-slate-500">
                        Belum ada tagihan pemeriksaan.
                    </p>
                </div>
            </div>
        @endforelse

    </div>

</x-layouts.app>