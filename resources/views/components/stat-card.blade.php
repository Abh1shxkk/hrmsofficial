@props(['title', 'value', 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'bg-blue-50 text-blue-700',
        'green' => 'bg-green-50 text-green-700',
        'red' => 'bg-red-50 text-red-700',
        'yellow' => 'bg-yellow-50 text-yellow-700',
        'purple' => 'bg-purple-50 text-purple-700',
    ];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    <p class="text-sm text-gray-500 mb-1">{{ $title }}</p>
    <p class="text-2xl font-bold {{ $colorClass }} inline-block px-2 py-1 rounded">{{ $value }}</p>
</div>
