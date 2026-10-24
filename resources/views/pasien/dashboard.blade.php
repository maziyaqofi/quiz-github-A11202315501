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
        <div id="active-queue-banner"
            class="card bg-base-100 shadow-md rounded-2xl border border-blue-200 mb-8">
            <div class="card-body">

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-bold text-slate-800">
                        <i class="fas fa-ticket-alt text-blue-600 mr-2"></i>
                        Antrian Aktif Anda
                    </h3>

                    @php
                        // Define queue status appearance
                        $queueStatusConfig = match ($antrianAktif->status_antrian) {
                            'dilayani' => ['label' => 'Sedang Dilayani', 'class' => 'badge-success'],
                            'dilewati' => ['label' => 'Dilewati', 'class' => 'badge-error'],
                            default => ['label' => 'Menunggu', 'class' => 'badge-warning'],
                        };
                    @endphp

                    <span id="queue-status-badge"
                        class="badge {{ $queueStatusConfig['class'] }}">
                        {{ $queueStatusConfig['label'] }}
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

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
                        <p id="current-queue-number"
                        class="text-4xl font-extrabold text-emerald-700">
                            {{ $nomorSedangDilayani ?? '-' }}
                        </p>
                    </div>

                    {{-- Remaining waiting patients ahead --}}
                    <div class="bg-amber-50 rounded-xl p-5 text-center">
                        <p class="text-sm font-medium text-amber-700 mb-2">
                            Sisa Antrean Sebelum Anda
                        </p>

                        <p id="remaining-queue-count"
                        class="text-4xl font-extrabold text-amber-700">
                            {{ $sisaAntrian ?? '-' }}
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
                            <th class="px-6 py-4">No</th>
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

                                <td class="px-6 py-4">
                                    {{ $loop->iteration }}
                                </td>

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

                                <td class="px-6 py-4 text-center"
                                    data-queue-schedule="{{ $jadwal->id }}">
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
                                <td colspan="5" class="text-center py-12 text-slate-400">
                                    Belum ada jadwal pemeriksaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Real-time Queue Listener --}}
    <script type="module">
        const echo = window.Echo;
        const queueStatusUrl = @json(route('dashboard.queue-status'));

        // Fetch the latest queue data from Laravel
        async function refreshQueueStatus() {
            try {
                const response = await fetch(queueStatusUrl, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error(`HTTP error: ${response.status}`);
                }

                const data = await response.json();

                // Hide the active queue banner when the examination is completed
                const activeQueueBanner = document.getElementById('active-queue-banner');

                if (activeQueueBanner && !data.antrian_aktif) {
                    activeQueueBanner.remove();
                }

                // Update the patient's active queue status
                const statusBadge = document.getElementById('queue-status-badge');

                if (statusBadge && data.antrian_aktif) {
                    const status = data.antrian_aktif.status;

                    const statusConfig = {
                        menunggu: {
                            label: 'Menunggu',
                            className: 'badge-warning',
                        },
                        dilayani: {
                            label: 'Sedang Dilayani',
                            className: 'badge-success',
                        },
                        dilewati: {
                            label: 'Dilewati',
                            className: 'badge-error',
                        },
                    };

                    const config = statusConfig[status] ?? statusConfig.menunggu;

                    statusBadge.textContent = config.label;

                    statusBadge.classList.remove(
                        'badge-success',
                        'badge-warning',
                        'badge-error'
                    );

                    statusBadge.classList.add(config.className);
                }

                // Update the currently served queue number
                const currentQueueNumber = document.getElementById('current-queue-number');

                if (currentQueueNumber) {
                    currentQueueNumber.textContent =
                        data.nomor_sedang_dilayani ?? '-';
                }

                // Update the number of waiting patients ahead
                const remainingQueueCount = document.getElementById('remaining-queue-count');

                if (remainingQueueCount) {
                    remainingQueueCount.textContent = data.sisa_antrian ?? '-';
                }

                // Update queue monitoring for every schedule
                document.querySelectorAll('[data-queue-schedule]').forEach((cell) => {
                    const scheduleId = cell.dataset.queueSchedule;
                    const queueNumber = data.antrian_dilayani[scheduleId];

                    cell.replaceChildren();

                    const span = document.createElement('span');

                    if (queueNumber !== undefined && queueNumber !== null) {
                        span.className = 'badge badge-success font-bold';
                        span.textContent = queueNumber;
                    } else {
                        span.className = 'text-slate-400';
                        span.textContent = 'Belum ada';
                    }

                    cell.appendChild(span);
                });

                console.log('Queue dashboard updated:', data);

            } catch (error) {
                console.error('Failed to update queue dashboard:', error);
            }
        }

        if (echo) {
            echo.channel('queue-updates')
                .listen('.QueueUpdated', (event) => {
                    console.log('Queue updated:', event);

                    refreshQueueStatus();
                });
        } else {
            console.error('Laravel Echo is not available.');
        }

        // Load the latest queue data when the dashboard opens
        refreshQueueStatus();

    </script>


</x-layouts.app>