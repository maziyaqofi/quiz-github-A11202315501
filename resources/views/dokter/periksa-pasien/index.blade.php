
<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
        <p class="text-slate-500 mt-1">
            Daftar antrean pemeriksaan hari ini,
            {{ now()->translatedFormat('d F Y') }}.
        </p>
    </div>

    {{-- Validation Error Alert --}}
    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 rounded-xl">
            <i class="fas fa-exclamation-circle"></i>

            <div>
                <h3 class="font-bold">
                    Tindakan Tidak Dapat Dilakukan
                </h3>

                @foreach($errors->all() as $error)
                    <p class="text-sm">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Daftar Pasien --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">

                <table class="table table-zebra w-full">
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Nomor Antrian</th>
                            <th class="px-6 py-4">Urutan Panggil</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($daftarPasien as $daftar)
                            <tr class="hover:bg-slate-50 transition">

                                <td class="px-6 py-4">
                                    {{ $daftar->id }}
                                </td>

                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $daftar->pasien->nama ?? '-' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $daftar->keluhan ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="badge badge-info badge-outline">
                                        {{ $daftar->no_antrian }}
                                    </span>
                                </td>

                                {{-- Current Call Order --}}
                                <td class="px-6 py-4">
                                    <span class="badge badge-primary badge-outline">
                                        {{ $daftar->urutan_panggil ?? '-' }}
                                    </span>
                                </td>

                                {{-- Queue Status --}}
                                <td class="px-6 py-4">
                                    @if($daftar->status_antrian === 'selesai' || $daftar->periksas->isNotEmpty())
                                        <span class="badge badge-success">
                                            Selesai
                                        </span>

                                    @elseif($daftar->status_antrian === 'dilayani')
                                        <span class="badge badge-info">
                                            Sedang Dilayani
                                        </span>

                                    @elseif($daftar->status_antrian === 'menunggu')
                                        <span class="badge badge-warning">
                                            Menunggu
                                        </span>
                                    
                                    @elseif($daftar->status_antrian === 'dilewati')
                                        <span class="badge badge-error badge-outline">
                                            Dilewati
                                        </span>

                                    @else
                                        <span class="badge badge-ghost">
                                            Tidak Diketahui
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">

                                    @if($daftar->status_antrian === 'selesai' || $daftar->periksas->isNotEmpty())

                                        {{-- Examination completed --}}
                                        <span class="badge badge-success badge-outline">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Sudah Diperiksa
                                        </span>

                                    @elseif($daftar->status_antrian === 'dilayani')

                                        {{-- Patient is currently being served --}}
                                        <a href="{{ route('periksa-pasien.create', $daftar->id) }}"
                                        class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-4">
                                            <i class="fas fa-stethoscope"></i>
                                            Periksa
                                        </a>

                                    @elseif($daftar->status_antrian === 'menunggu')
                                        <div class="flex justify-end gap-2">
                                            {{-- Start serving patient --}}
                                            <form action="{{ route('periksa-pasien.mulai-layani', $daftar->id) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-warning rounded-lg">
                                                    <i class="fas fa-play"></i>
                                                    Mulai Layani
                                                </button>
                                            </form>

                                            {{-- Skip patient --}}
                                            <form action="{{ route('periksa-pasien.lewati', $daftar->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Yakin ingin melewati antrean pasien ini?')">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline rounded-lg">
                                                    <i class="fas fa-forward"></i>
                                                    Lewati
                                                </button>
                                            </form>
                                        </div>

                                    @elseif($daftar->status_antrian === 'dilewati')
                                        {{-- Reactivate skipped patient --}}
                                        <form action="{{ route('periksa-pasien.aktifkan-kembali', $daftar->id) }}"
                                            method="POST"
                                            class="inline-block">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-info rounded-lg">
                                                <i class="fas fa-rotate-right"></i>
                                                Aktifkan Kembali
                                            </button>
                                        </form>

                                    @else

                                        <span class="badge badge-ghost">
                                            Status Tidak Dikenal
                                        </span>

                                    @endif

                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-14 text-slate-400">
                                    <i class="fas fa-user-doctor text-3xl mb-3 block"></i>
                                    Belum ada antrean pasien untuk hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</x-layouts.app>
