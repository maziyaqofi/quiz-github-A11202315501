<x-layouts.app title="Riwayat Pendaftaran">
    <div class="space-y-6">

        {{-- Page Header --}}
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Riwayat Pendaftaran Poli
            </h1>
            <p class="text-slate-500 mt-1">
                Lihat seluruh riwayat pendaftaran dan status pemeriksaan Anda.
            </p>
        </div>

        {{-- History Table --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th>No.</th>
                                <th>Poli</th>
                                <th>Dokter</th>
                                <th>Jadwal</th>
                                <th>No. Antrian</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayatPendaftaran as $pendaftaran)
                                @php
                                    $jadwal = $pendaftaran->jadwalPeriksa;
                                    $dokter = $jadwal?->dokter;
                                    $sudahDiperiksa = $pendaftaran->periksas->isNotEmpty();
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $dokter?->poli?->nama_poli ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $dokter?->nama ?? '-' }}
                                    </td>

                                    <td>
                                        @if ($jadwal)
                                            <div class="flex flex-col gap-1">
                                                <span class="font-semibold">
                                                    @if ($pendaftaran->tanggal_kunjungan)
                                                        {{ $pendaftaran->tanggal_kunjungan->format('d/m/Y') }}
                                                    @else
                                                        Tanggal belum tersedia
                                                    @endif
                                                </span>

                                                <span class="text-sm text-slate-500">
                                                    {{ $jadwal->hari }},
                                                    {{ substr($jadwal->jam_mulai, 0, 5) }}
                                                    -
                                                    {{ substr($jadwal->jam_selesai, 0, 5) }}
                                                </span>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <span class="font-semibold">
                                            {{ $pendaftaran->no_antrian }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($sudahDiperiksa)
                                            <span class="badge badge-success text-white">
                                                Sudah Diperiksa
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                Menunggu
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($sudahDiperiksa)
                                            <a href="{{ route('pasien.riwayat-pendaftaran.show', $pendaftaran->id) }}"
                                            class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none">
                                                <i class="fas fa-eye"></i>
                                                Detail
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-sm">
                                                Belum tersedia
                                            </span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-slate-500">
                                        <i class="fas fa-clipboard-list text-3xl mb-3 block"></i>
                                        Belum ada riwayat pendaftaran poli.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
