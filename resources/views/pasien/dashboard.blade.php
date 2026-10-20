
<x-layouts.app title="Dashboard Pasien">

    {{-- Page Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Dashboard Pasien
        </h2>
        <p class="text-slate-500 mt-1">
            Pantau informasi pendaftaran dan antrian pemeriksaan Anda.
        </p>
    </div>

    {{-- Active Queue Banner --}}
    @if($antrianAktif)
        <div class="card bg-base-100 shadow-md rounded-2xl border border-blue-200 mb-8">
            <div class="card-body">

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-bold text-slate-800">
                        <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
                        Antrian Aktif Anda
                    </h3>

                    <span class="badge {{ $antrianAktif->status_antrian === 'dilayani' ? 'badge-success' : 'badge-warning' }}">
                        {{ $antrianAktif->status_antrian === 'dilayani' ? 'Sedang Dilayani' : 'Menunggu' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">

                    <div>
                        <p class="text-sm text-slate-500">Poli</p>
                        <p class="font-semibold text-slate-800">
                            {{ $antrianAktif->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Dokter</p>
                        <p class="font-semibold text-slate-800">
                            {{ $antrianAktif->jadwalPeriksa?->dokter?->nama ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Jadwal Pemeriksaan</p>
                        <p class="font-semibold text-slate-800">
                            {{ $antrianAktif->jadwalPeriksa?->hari ?? '-' }},
                            {{ $antrianAktif->jadwalPeriksa?->jam_mulai ?? '-' }}
                            -
                            {{ $antrianAktif->jadwalPeriksa?->jam_selesai ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Tanggal Kunjungan</p>
                        <p class="font-semibold text-slate-800">
                            {{ $antrianAktif->tanggal_kunjungan?->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="bg-blue-50 rounded-xl p-5 text-center">
                        <p class="text-sm font-medium text-blue-700 mb-2">
                            Nomor Antrian Anda
                        </p>
                        <p class="text-4xl font-extrabold text-blue-700">
                            {{ $antrianAktif->no_antrian }}
                        </p>
                    </div>

                    <div class="bg-emerald-50 rounded-xl p-5 text-center">
                        <p class="text-sm font-medium text-emerald-700 mb-2">
                            Nomor Sedang Dilayani
                        </p>
                        <p class="text-4xl font-extrabold text-emerald-700">
                            {{ $nomorSedangDilayani ?? '-' }}
                        </p>
                    </div>

                </div>

            </div>
        </div>
    @endif

    {{-- Queue Monitoring Table --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">

            <div class="px-6 pt-6 pb-4">
                <h3 class="text-lg font-bold text-slate-800">
                    <i class="fas fa-list-ol text-blue-600 mr-2"></i>
                    Monitoring Antrian Poli
                </h3>
                <p class="text-sm text-slate-500 mt-1">
                    Informasi nomor antrian yang sedang dilayani hari ini.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Jadwal</th>
                            <th class="px-6 py-4 text-center">
                                Sedang Dilayani
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($jadwals as $jadwal)
                            <tr class="hover:bg-slate-50">

                                <td class="px-6 py-4 font-semibold">
                                    {{ $jadwal->dokter?->poli?->nama_poli ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $jadwal->dokter?->nama ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $jadwal->hari }},
                                    {{ $jadwal->jam_mulai }}
                                    -
                                    {{ $jadwal->jam_selesai }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @php
                                        $sedangDilayani = $jadwal->daftarPolis->first();
                                    @endphp

                                    @if($sedangDilayani)
                                        <span class="badge badge-success font-bold">
                                            {{ $sedangDilayani->no_antrian }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">
                                            Belum ada
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-12 text-slate-400">
                                    Belum ada jadwal pemeriksaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
