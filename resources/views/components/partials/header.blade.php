<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i data-lucide="chevron-right" class="w-4 h-4 text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- Notification Bell --}}
    @auth
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();

            $notifications = auth()->user()
                ->notifications()
                ->latest()
                ->limit(5)
                ->get();
        @endphp

        <div class="dropdown dropdown-end">

            {{-- Bell Button --}}
            <div
                tabindex="0"
                role="button"
                class="btn btn-square btn-ghost relative"
                aria-label="Notifikasi"
            >
                <i class="fas fa-bell text-lg"></i>

                @if($unreadCount > 0)
                    <span
                        class="absolute -top-1 -right-1 min-w-5 h-5 px-1
                            rounded-full bg-red-600 text-white text-xs
                            flex items-center justify-center font-bold"
                        style="background-color: #dc2626; color: white;"
                    >
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </div>

            {{-- Notification Dropdown --}}
            <div
                tabindex="0"
                class="dropdown-content bg-base-100 rounded-xl shadow-xl
                    border border-base-300 w-80 sm:w-96 max-w-[90vw]
                    mt-3 z-50"
            >
                {{-- Notification Dropdown Header --}}
                <div class="p-4 border-b border-base-300">

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-base">
                                Notifikasi
                            </h3>

                            <p class="text-xs text-base-content/60 mt-1">
                                {{ $unreadCount }} notifikasi belum dibaca
                            </p>
                        </div>

                        @if($unreadCount > 0)
                            <form
                                action="{{ route('notifications.read-all') }}"
                                method="POST"
                            >
                                @csrf
                                @method('PATCH')

                                <button
                                    type="submit"
                                    class="text-xs font-semibold whitespace-nowrap hover:underline"
                                    style="color: #2563eb;"
                                >
                                    <i class="fas fa-check-double mr-1"></i>
                                    Tandai Semua
                                </button>
                            </form>
                        @endif

                    </div>
                </div>

                <div class="max-h-80 overflow-y-auto">

                    @forelse($notifications as $notification)
                        <div
                            class="p-4 border-b border-base-200"
                            style="{{ is_null($notification->read_at)
                                ? 'background-color: rgba(59, 130, 246, 0.08);'
                                : '' }}"
                        >
                            <div class="flex gap-3">

                                <i class="fas fa-circle-info text-primary mt-1"></i>

                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </p>

                                    <p class="text-xs text-base-content/70 mt-1">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>

                                    <p class="text-xs text-base-content/50 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>

                                    @if(is_null($notification->read_at))
                                        <form
                                            action="{{ route('notifications.read', $notification->id) }}"
                                            method="POST"
                                            class="mt-2"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="text-xs font-semibold underline"
                                                style="color: #2563eb;"
                                            >
                                                <i class="fas fa-check mr-1"></i>
                                                Tandai sudah dibaca
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-base-content/50">
                                            <i class="fas fa-check-double mr-1"></i>
                                            Sudah dibaca
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-base-content/50">
                            <i class="far fa-bell text-3xl mb-3"></i>
                            <p class="text-sm">
                                Belum ada notifikasi.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    @endauth

    {{-- User Info --}}
    <div class="flex items-center gap-3">

        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight">
                {{ auth()->user()->name ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight">
                {{ auth()->user()->role ?? 'Admin Sistem' }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                <span class="text-sm font-semibold leading-none">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </div>

</header>

<script>
    function toggleFullscreen() {
    const icon = document.getElementById('fsIcon');

    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
    } else {
        document.exitFullscreen();
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
    }
}
</script>