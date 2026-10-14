
<x-layouts.app title="Tambah Jadwal Periksa">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Tambah Jadwal Periksa
        </h2>
        <p class="text-slate-500 mt-1">
            Silakan isi formulir untuk menambahkan jadwal praktik.
        </p>
    </div>

    {{-- Card Form --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body">

            <form action="{{ route('jadwal-periksa.store') }}" method="POST">
                @csrf

                {{-- Hari --}}
                <div class="form-control mb-4">
                    <label for="hari" class="label">
                        <span class="label-text font-semibold">
                            Hari
                        </span>
                    </label>

                    <select
                        id="hari"
                        name="hari"
                        class="select select-bordered w-full
                        @error('hari') select-error @enderror"
                        required>

                        <option value="" disabled {{ old('hari') ? '' : 'selected' }}>
                            Pilih Hari
                        </option>

                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                            <option value="{{ $hari }}"
                                {{ old('hari') == $hari ? 'selected' : '' }}>
                                {{ $hari }}
                            </option>
                        @endforeach

                    </select>

                    @error('hari')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Jam Mulai --}}
                <div class="form-control mb-4">
                    <label for="jam_mulai" class="label">
                        <span class="label-text font-semibold">
                            Jam Mulai
                        </span>
                    </label>

                    <input
                        type="time"
                        id="jam_mulai"
                        name="jam_mulai"
                        value="{{ old('jam_mulai') }}"
                        class="input input-bordered w-full
                        @error('jam_mulai') input-error @enderror"
                        required>

                    @error('jam_mulai')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Jam Selesai --}}
                <div class="form-control mb-6">
                    <label for="jam_selesai" class="label">
                        <span class="label-text font-semibold">
                            Jam Selesai
                        </span>
                    </label>

                    <input
                        type="time"
                        id="jam_selesai"
                        name="jam_selesai"
                        value="{{ old('jam_selesai') }}"
                        class="input input-bordered w-full
                        @error('jam_selesai') input-error @enderror"
                        required>

                    @error('jam_selesai')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3">

                    <a href="{{ route('jadwal-periksa.index') }}"
                        class="btn bg-slate-200 hover:bg-slate-300
                        text-slate-700 border-none rounded-lg px-5">
                        <i class="fas fa-arrow-left"></i>
                        Batal
                    </a>

                    <button type="submit"
                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                        text-white border-none rounded-lg px-5">
                        <i class="fas fa-save"></i>
                        Simpan Jadwal
                    </button>

                </div>

            </form>

        </div>
    </div>

</x-layouts.app>
