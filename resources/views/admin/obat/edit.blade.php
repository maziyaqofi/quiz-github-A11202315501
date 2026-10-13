
<x-layouts.app title="Edit Obat">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Edit Obat
        </h2>
        <p class="text-slate-500 mt-1">
            Silakan ubah data obat yang ingin diperbarui.
        </p>
    </div>

    {{-- Card Form --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body">

            <form action="{{ route('obat.update', $obat->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Nama Obat --}}
                <div class="form-control mb-4">
                    <label for="nama_obat" class="label">
                        <span class="label-text font-semibold">
                            Nama Obat
                        </span>
                    </label>

                    <input
                        type="text"
                        id="nama_obat"
                        name="nama_obat"
                        value="{{ old('nama_obat', $obat->nama_obat) }}"
                        placeholder="Masukkan nama obat"
                        class="input input-bordered w-full
                        @error('nama_obat') input-error @enderror"
                        required>

                    @error('nama_obat')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Kemasan --}}
                <div class="form-control mb-4">
                    <label for="kemasan" class="label">
                        <span class="label-text font-semibold">
                            Kemasan
                        </span>
                    </label>

                    <input
                        type="text"
                        id="kemasan"
                        name="kemasan"
                        value="{{ old('kemasan', $obat->kemasan) }}"
                        placeholder="Contoh: Tablet, Kapsul, Sirup"
                        class="input input-bordered w-full
                        @error('kemasan') input-error @enderror"
                        required>

                    @error('kemasan')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Harga --}}
                <div class="form-control mb-6">
                    <label for="harga" class="label">
                        <span class="label-text font-semibold">
                            Harga (Rp)
                        </span>
                    </label>

                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        value="{{ old('harga', $obat->harga) }}"
                        placeholder="Masukkan harga obat"
                        min="0"
                        step="any"
                        class="input input-bordered w-full
                        @error('harga') input-error @enderror"
                        required>

                    @error('harga')
                    <span class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </span>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3">

                    <a href="{{ route('obat.index') }}"
                        class="btn bg-slate-200 hover:bg-slate-300
                        text-slate-700 border-none rounded-lg px-5">
                        <i class="fas fa-arrow-left"></i>
                        Batal
                    </a>

                    <button type="submit"
                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                        text-white border-none rounded-lg px-5">
                        <i class="fas fa-save"></i>
                        Update Obat
                    </button>

                </div>

            </form>

        </div>
    </div>

</x-layouts.app>
