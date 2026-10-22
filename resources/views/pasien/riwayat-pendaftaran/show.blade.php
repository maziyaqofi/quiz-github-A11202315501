<x-layouts.app title="Detail Riwayat Pendaftaran">

    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Detail Riwayat Pendaftaran
                </h1>
                <p class="text-slate-500 mt-1">
                    Informasi pendaftaran dan hasil pemeriksaan pasien.
                </p>
            </div>

            <a href="{{ route('pasien.riwayat-pendaftaran.index') }}"
               class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>

        {{-- Registration Information --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body">
                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    Informasi Pendaftaran
                </h2>

                @php
                    $jadwal = $pendaftaran->jadwalPeriksa;
                    $dokter = $jadwal?->dokter;
                    $periksa = $pendaftaran->periksas->first();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <p class="text-sm text-slate-500">Poli</p>
                        <p class="font-semibold">
                            {{ $dokter?->poli?->nama_poli ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Dokter</p>
                        <p class="font-semibold">
                            {{ $dokter?->nama ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Jadwal Pemeriksaan</p>

                        @if ($jadwal)
                            <p class="font-semibold">
                                @if ($pendaftaran->tanggal_kunjungan)
                                    {{ $pendaftaran->tanggal_kunjungan->format('d/m/Y') }}
                                @else
                                    Tanggal belum tersedia
                                @endif
                            </p>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $jadwal->hari }},
                                {{ substr($jadwal->jam_mulai, 0, 5) }}
                                -
                                {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </p>
                        @else
                            <p class="font-semibold">-</p>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Nomor Antrian</p>
                        <p class="font-semibold">
                            {{ $pendaftaran->no_antrian }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Keluhan</p>
                        <p class="font-semibold">
                            {{ $pendaftaran->keluhan ?: '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Status</p>

                        @if($periksa)
                            <span class="badge badge-success text-white">
                                Sudah Diperiksa
                            </span>
                        @else
                            <span class="badge badge-warning">
                                Menunggu Pemeriksaan
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- Examination Details --}}
        @if($periksa)

            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body">

                    <h2 class="text-lg font-bold text-slate-800 mb-4">
                        Hasil Pemeriksaan
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">

                        <div>
                            <p class="text-sm text-slate-500">
                                Tanggal Pemeriksaan
                            </p>
                            <p class="font-semibold">
                                {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Catatan Dokter
                            </p>
                            <p class="font-semibold whitespace-pre-line">
                                {{ $periksa->catatan ?: '-' }}
                            </p>
                        </div>

                    </div>

                    {{-- Medicine Table --}}
                    <h3 class="font-semibold text-slate-800 mb-3">
                        Obat yang Diberikan
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Obat</th>
                                    <th>Kemasan</th>
                                    <th>Harga</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($periksa->detailPeriksas as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->obat?->nama_obat ?? '-' }}</td>
                                        <td>{{ $detail->obat?->kemasan ?? '-' }}</td>
                                        <td>
                                            Rp {{ number_format($detail->obat?->harga ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-6 text-slate-500">
                                            Tidak ada obat yang diberikan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Total Cost --}}
                    <div class="flex justify-end mt-6">
                        <div class="bg-slate-50 rounded-xl p-4 min-w-64">
                            <p class="text-sm text-slate-500">
                                Total Biaya Pemeriksaan
                            </p>
                            <p class="text-2xl font-bold text-[#2d4499] mt-1">
                                Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Payment Information --}}
            <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
                <div class="card-body">

                    <h2 class="text-lg font-bold text-slate-800 mb-4">
                        Informasi Pembayaran
                    </h2>

                    @php
                        $pembayaran = $periksa->pembayaran;
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <p class="text-sm text-slate-500">
                                Total Tagihan
                            </p>
                            <p class="text-xl font-bold text-[#2d4499]">
                                Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Status Pembayaran
                            </p>

                            @if(!$pembayaran || !$pembayaran->tanggal_upload)
                                <span class="badge badge-warning">
                                    Belum Bayar
                                </span>
                            @elseif($pembayaran->status === 'menunggu')
                                <span class="badge badge-info text-white">
                                    Menunggu Verifikasi
                                </span>
                            @elseif($pembayaran->status === 'lunas')
                                <span class="badge badge-success text-white">
                                    Lunas
                                </span>
                            @elseif($pembayaran->status === 'ditolak')
                                <span class="badge badge-error text-white">
                                    Ditolak
                                </span>
                            @endif
                        </div>

                    </div>

                    {{-- Upload Payment Proof --}}
                    @if(!$pembayaran || !$pembayaran->tanggal_upload || $pembayaran->status === 'ditolak')

                        <div class="mt-6 border-t border-slate-200 pt-5">

                            <h3 class="font-semibold text-slate-800 mb-2">
                                Upload Bukti Pembayaran
                            </h3>

                            <p class="text-sm text-slate-500 mb-4">
                                Unggah bukti pembayaran setelah melakukan pembayaran.
                                Format yang diperbolehkan: JPG, JPEG, atau PNG.
                                Maksimal ukuran file 2 MB.
                            </p>

                            <form action="{{ route('pasien.pembayaran.upload', $periksa->id) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="space-y-4">

                                @csrf

                                <input
                                    type="file"
                                    name="bukti_pembayaran"
                                    accept=".jpg,.jpeg,.png"
                                    class="file-input file-input-bordered w-full max-w-md"
                                    required
                                >

                                @error('bukti_pembayaran')
                                    <p class="text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <div>
                                    <button type="submit"
                                            class="btn bg-[#2d4499] hover:bg-[#23377d] text-white border-none">
                                        <i class="fas fa-upload"></i>
                                        Upload Bukti Pembayaran
                                    </button>
                                </div>

                            </form>

                        </div>

                    @elseif($pembayaran->status === 'menunggu')

                        <div class="alert alert-info mt-6">
                            <i class="fas fa-clock"></i>
                            <span>
                                Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi admin.
                            </span>
                        </div>

                    @elseif($pembayaran->status === 'lunas')

                        <div class="alert alert-success mt-6">
                            <i class="fas fa-check-circle"></i>
                            <span>
                                Pembayaran telah diverifikasi dan dinyatakan lunas.
                            </span>
                        </div>

                    @endif

                </div>
            </div>

        @else

            <div class="alert bg-amber-50 text-amber-800 border border-amber-200">
                <i class="fas fa-clock"></i>
                <span>
                    Pemeriksaan belum dilakukan. Hasil pemeriksaan akan tersedia
                    setelah dokter menyelesaikan pemeriksaan.
                </span>
            </div>

        @endif

    </div>

</x-layouts.app>
