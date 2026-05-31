<header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
    <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>

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
            <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</button>
        </form>
    </div>
</header>
