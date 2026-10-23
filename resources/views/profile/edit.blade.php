<x-layouts.app title="Profile">

    {{-- Page Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Profile Pengguna
        </h2>
        <p class="text-slate-500 mt-1">
            Kelola informasi akun dan keamanan password Anda.
        </p>
    </div>

    {{-- Profile Information --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 mb-6">
        <div class="card-body">
            <h3 class="text-lg font-bold text-slate-800 mb-4">
                Informasi Akun
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-slate-500">Nama</p>
                    <p class="font-semibold">{{ $user->nama }}</p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Role</p>
                    <p class="font-semibold capitalize">{{ $user->role }}</p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Email</p>
                    <p class="font-semibold">{{ $user->email ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body">

            <h3 class="text-lg font-bold text-slate-800 mb-2">
                Ganti Password
            </h3>

            <p class="text-sm text-slate-500 mb-5">
                Gunakan password baru minimal 8 karakter.
            </p>

            <form action="{{ route('profile.password.update') }}"
                  method="POST"
                  class="space-y-5">

                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-sm font-semibold mb-2">
                        Password Lama
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="input input-bordered w-full @error('current_password') input-error @enderror"
                        autocomplete="current-password"
                        required
                    >

                    @error('current_password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-semibold mb-2">
                        Password Baru
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        autocomplete="new-password"
                        required
                    >

                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold mb-2">
                        Konfirmasi Password Baru
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="input input-bordered w-full @error('password_confirmation') input-error @enderror"
                        autocomplete="new-password"
                        required
                    >

                    @error('password_confirmation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg">
                        <i class="fas fa-lock mr-2"></i>
                        Simpan Password Baru
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layouts.app>