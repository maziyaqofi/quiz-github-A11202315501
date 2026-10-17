
<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
        <p class="text-slate-500 mt-1">
            Daftar pasien yang terdaftar untuk pemeriksaan.
        </p>
    </div>

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

                                <td class="px-6 py-4 text-right">
                                    @if($daftar->periksas->isNotEmpty())
                                        <span class="badge badge-success badge-outline">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Sudah Diperiksa
                                        </span>
                                    @else
                                        <a href="{{ route('periksa-pasien.create', $daftar->id) }}"
                                           class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-4">
                                            <i class="fas fa-stethoscope"></i>
                                            Periksa
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-14 text-slate-400">
                                    <i class="fas fa-user-doctor text-3xl mb-3 block"></i>
                                    Belum ada pasien yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</x-layouts.app>
