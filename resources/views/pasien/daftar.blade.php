
<x-layouts.app title="Pendaftaran Periksa">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Pendaftaran Periksa
        </h2>
        <p class="text-slate-500 mt-1">
            Pilih poli, jadwal dokter, dan isi keluhan untuk mendaftar pemeriksaan.
        </p>
    </div>

    {{-- Form Pendaftaran --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body">

            <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                @csrf

                {{-- Informasi Pasien --}}
                <div class="mb-5">
                    <label class="label">
                        <span class="label-text font-semibold">Nama Pasien</span>
                    </label>

                    <input type="text"
                           value="{{ $user->nama }}"
                           class="input input-bordered w-full bg-slate-100"
                           readonly>
                </div>

                {{-- Pilih Poli --}}
                <div class="mb-5">
                    <label for="id_poli" class="label">
                        <span class="label-text font-semibold">Pilih Poli</span>
                    </label>

                    <select name="id_poli"
                            id="id_poli"
                            class="select select-bordered w-full"
                            required>
                        <option value="">-- Pilih Poli --</option>

                        @foreach($polis as $poli)
                            <option value="{{ $poli->id }}"
                                {{ old('id_poli') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->nama_poli }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_poli')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pilih Jadwal --}}
                <div class="mb-5">
                    <label for="id_jadwal" class="label">
                        <span class="label-text font-semibold">Jadwal Dokter</span>
                    </label>

                    <select name="id_jadwal"
                            id="id_jadwal"
                            class="select select-bordered w-full"
                            required
                            disabled>
                        <option value="">-- Pilih poli terlebih dahulu --</option>

                        @foreach($jadwals as $jadwal)
                            <option value="{{ $jadwal->id }}"
                                    data-poli="{{ $jadwal->dokter?->poli?->id }}"
                                    {{ old('id_jadwal') == $jadwal->id ? 'selected' : '' }}>
                                {{ $jadwal->dokter?->nama ?? 'Dokter' }}
                                - {{ $jadwal->hari }}
                                ({{ substr($jadwal->jam_mulai, 0, 5) }}
                                - {{ substr($jadwal->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>

                    @error('id_jadwal')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Keluhan --}}
                <div class="mb-6">
                    <label for="keluhan" class="label">
                        <span class="label-text font-semibold">Keluhan</span>
                    </label>

                    <textarea name="keluhan"
                              id="keluhan"
                              rows="4"
                              class="textarea textarea-bordered w-full"
                              placeholder="Tuliskan keluhan yang sedang dialami...">{{ old('keluhan') }}</textarea>

                    @error('keluhan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol --}}
                <div class="flex justify-end">
                    <button type="submit"
                            class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
                        <i class="fas fa-clipboard-check"></i>
                        Daftar Periksa
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Filter Jadwal Berdasarkan Poli --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const poliSelect = document.getElementById('id_poli');
            const jadwalSelect = document.getElementById('id_jadwal');

            const jadwalOptions = Array.from(jadwalSelect.querySelectorAll('option'))
                .filter(option => option.value !== '');

            function filterJadwal() {
                const selectedPoli = poliSelect.value;
                const previousJadwal = jadwalSelect.value;

                jadwalSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = selectedPoli
                    ? '-- Pilih Jadwal Dokter --'
                    : '-- Pilih poli terlebih dahulu --';

                jadwalSelect.appendChild(placeholder);
                jadwalSelect.disabled = !selectedPoli;

                jadwalOptions.forEach(function (option) {
                    if (option.dataset.poli === selectedPoli) {
                        jadwalSelect.appendChild(option);
                    }
                });

                const stillAvailable = Array.from(jadwalSelect.options)
                    .some(option => option.value === previousJadwal);

                if (stillAvailable) {
                    jadwalSelect.value = previousJadwal;
                }
            }

            poliSelect.addEventListener('change', function () {
                jadwalSelect.value = '';
                filterJadwal();
            });

            filterJadwal();
        });
    </script>

</x-layouts.app>
