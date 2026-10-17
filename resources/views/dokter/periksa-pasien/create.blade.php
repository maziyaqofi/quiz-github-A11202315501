
<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Pemeriksaan Pasien
        </h2>
        <p class="text-slate-500 mt-1">
            Isi hasil pemeriksaan dan obat yang diberikan kepada pasien.
        </p>
    </div>

    {{-- Form Pemeriksaan --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body">

            <form action="{{ route('periksa-pasien.store') }}" method="POST">
                @csrf

                {{-- ID Pendaftaran --}}
                <input type="hidden"
                       name="id_daftar_poli"
                       value="{{ $id }}">

                {{-- Catatan Pemeriksaan --}}
                <div class="form-control mb-4">
                    <label for="catatan" class="label">
                        <span class="label-text font-semibold">
                            Catatan Pemeriksaan
                        </span>
                    </label>

                    <textarea
                        id="catatan"
                        name="catatan"
                        rows="4"
                        class="textarea textarea-bordered w-full
                        @error('catatan') textarea-error @enderror"
                        placeholder="Masukkan hasil pemeriksaan pasien...">{{ old('catatan') }}</textarea>

                    @error('catatan')
                        <span class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Pilihan Obat --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">
                            Pilih Obat
                        </span>
                    </label>

                    <div class="border border-slate-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                        @forelse($obats as $obat)
                            <label class="flex items-center gap-3 py-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="checkbox checkbox-primary checkbox-sm obat-checkbox"
                                    value="{{ $obat->id }}"
                                    data-harga="{{ $obat->harga }}">

                                <span class="flex-1 text-slate-700">
                                    {{ $obat->nama_obat }}
                                    <span class="text-slate-400 text-sm">
                                        ({{ $obat->kemasan }})
                                    </span>
                                </span>

                                <span class="text-sm font-semibold text-slate-600">
                                    Rp {{ number_format($obat->harga, 0, ',', '.') }}
                                </span>
                            </label>
                        @empty
                            <p class="text-slate-400 text-sm">
                                Belum ada obat yang tersedia.
                            </p>
                        @endforelse
                    </div>

                    <input type="hidden"
                           name="obat_json"
                           id="obat_json"
                           value="{{ old('obat_json', '[]') }}">

                    @error('obat_json')
                        <span class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Rincian Biaya --}}
                <div class="bg-slate-50 rounded-xl p-4 mb-6 space-y-3">

                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Pemeriksaan</span>
                        <span>Rp 150.000</span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>Total Harga Obat</span>
                        <span id="total-obat">Rp 0</span>
                    </div>

                    <div class="border-t border-slate-200 pt-3 flex justify-between font-bold text-slate-800">
                        <span>Total Biaya</span>
                        <span id="total-biaya">Rp 150.000</span>
                    </div>

                </div>

                <input type="hidden"
                       name="biaya_periksa"
                       id="biaya_periksa"
                       value="0">

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3">

                    <a href="{{ route('periksa-pasien.index') }}"
                       class="btn bg-slate-200 hover:bg-slate-300 text-slate-700 border-none rounded-lg px-5">
                        <i class="fas fa-arrow-left"></i>
                        Batal
                    </a>

                    <button type="submit"
                            class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-5">
                        <i class="fas fa-save"></i>
                        Simpan Pemeriksaan
                    </button>

                </div>

            </form>
        </div>
    </div>

    {{-- Perhitungan Obat --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.obat-checkbox');
            const obatJson = document.getElementById('obat_json');
            const biayaPeriksa = document.getElementById('biaya_periksa');
            const totalObat = document.getElementById('total-obat');
            const totalBiaya = document.getElementById('total-biaya');

            const formatRupiah = (value) => {
                return 'Rp ' + value.toLocaleString('id-ID');
            };

            function hitungBiaya() {
                let selectedIds = [];
                let totalHargaObat = 0;

                checkboxes.forEach(function (checkbox) {
                    if (checkbox.checked) {
                        selectedIds.push(Number(checkbox.value));
                        totalHargaObat += Number(checkbox.dataset.harga);
                    }
                });

                obatJson.value = JSON.stringify(selectedIds);
                biayaPeriksa.value = totalHargaObat;

                totalObat.textContent = formatRupiah(totalHargaObat);
                totalBiaya.textContent = formatRupiah(totalHargaObat + 150000);
            }

            // Restore pilihan obat setelah validasi gagal
            let oldIds = [];

            try {
                oldIds = JSON.parse(obatJson.value || '[]');
            } catch (error) {
                oldIds = [];
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = oldIds.includes(Number(checkbox.value));
                checkbox.addEventListener('change', hitungBiaya);
            });

            hitungBiaya();
        });
    </script>

</x-layouts.app>
