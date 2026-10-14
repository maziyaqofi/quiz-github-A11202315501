
<x-layouts.app title="Jadwal Periksa">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Jadwal Periksa
        </h2>

        <a href="{{ route('jadwal-periksa.create') }}"
            class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                   text-white border-none rounded-lg px-5">
            <i class="fas fa-plus"></i>
            Tambah Jadwal
        </a>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 overflow-hidden">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    {{-- Table Header --}}
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Mulai</th>
                            <th class="px-6 py-4">Jam Selesai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Table Body --}}
                    <tbody>
                        @forelse($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50 transition">

                            {{-- Hari --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $jadwal->hari }}
                            </td>

                            {{-- Jam Mulai --}}
                            <td class="px-6 py-4 text-slate-600">
                                {{ substr($jadwal->jam_mulai, 0, 5) }}
                            </td>

                            {{-- Jam Selesai --}}
                            <td class="px-6 py-4 text-slate-600">
                                {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                <span class="badge badge-success badge-outline">
                                    <i class="fas fa-circle-check mr-1"></i>
                                    Terjadwal
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('jadwal-periksa.edit', $jadwal->id) }}"
                                        class="btn btn-sm bg-amber-500 hover:bg-amber-600
                                               text-white border-none rounded-lg px-4">
                                        <i class="fas fa-pen-to-square"></i>
                                        Edit
                                    </a>

                                    {{-- Hapus --}}
                                    <form
                                        action="{{ route('jadwal-periksa.destroy', $jadwal->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus jadwal ini?')"
                                            class="btn btn-sm bg-red-500 hover:bg-red-600
                                                   text-white border-none rounded-lg px-4">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5"
                                class="text-center py-14 text-slate-400">
                                <i class="fas fa-calendar-xmark text-3xl mb-3 block"></i>
                                Belum ada jadwal periksa
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>
