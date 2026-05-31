<header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 sm:px-6">
    <div class="flex items-center space-x-3">
        {{-- Hamburger (mobile only) opens the sidebar drawer --}}
        <button @click="mobileOpen = true" class="md:hidden text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="text-base sm:text-lg font-semibold text-gray-800 truncate">@yield('title', 'Dashboard')</h1>
    </div>

    <div class="flex items-center space-x-4">
        <a href="{{ route('profile.show') }}" class="flex items-center space-x-2 hover:opacity-80">
            @php $emp = auth()->user()->employee; @endphp
            @if($emp?->photo)
                <img src="{{ Storage::url($emp->photo) }}" class="w-8 h-8 rounded-full object-cover">
            @else
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
            @endif
            <span class="hidden sm:inline text-sm text-gray-600">{{ auth()->user()->name }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
        </form>
    </div>
</header>
