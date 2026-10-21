<x-layouts.app title="Manajemen Pembayaran">

    <div class="space-y-6">

        {{-- Page Header --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Manajemen Pembayaran
            </h1>
            <p class="text-slate-500 mt-1">
                Kelola dan verifikasi bukti pembayaran pasien.
            </p>
        </div>

        {{-- Payment Table --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body">

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">

                        <thead class="bg-slate-100">
                            <tr>
                                <th>No.</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Total Tagihan</th>
                                <th>Status</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($pembayarans as $pembayaran)
                                @php
                                    $periksa = $pembayaran->periksa;
                                    $pendaftaran = $periksa?->daftarPoli;
                                    $pasien = $pendaftaran?->pasien;
                                    $dokter = $pendaftaran?->jadwalPeriksa?->dokter;
                                @endphp

                                <tr>
                                    <td>
                                        {{ $pembayarans->firstItem() + $loop->index }}
                                    </td>

                                    <td>
                                        {{ $pasien?->nama ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $dokter?->nama ?? '-' }}
                                    </td>

                                    <td class="font-semibold">
                                        Rp {{ number_format($periksa?->biaya_periksa ?? 0, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        @if($pembayaran->status === 'menunggu')
                                            <span class="badge badge-warning">
                                                Menunggu
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
                                    </td>

                                    <td>
                                        {{ $pembayaran->tanggal_upload?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        <a href="{{ route('admin.pembayaran.show', $pembayaran->id) }}"
                                           class="btn btn-sm btn-outline btn-primary">
                                            <i class="fas fa-eye"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-500">
                                        Belum ada data pembayaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $pembayarans->links() }}
                </div>

            </div>
        </div>

    </div>

</x-layouts.app>