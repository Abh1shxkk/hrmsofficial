@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70">
    <div class="border-b border-slate-100 px-8 py-7">
        <div class="mb-5 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-600 text-base font-bold text-white">
                HR
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-950">HRMS Portal</h1>
                <p class="text-sm text-slate-500">Secure access for HR, managers and employees</p>
            </div>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Sign in</h2>
            <p class="mt-1 text-sm text-slate-500">Use your registered work email and password.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5 px-8 py-7">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   placeholder="admin@hrms.com"
                   autocomplete="email"
                   class="form-control">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ showPassword: false }">
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                       placeholder="Enter your password"
                       autocomplete="current-password"
                       class="form-control pr-20">
                <button type="button"
                        class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-blue-700"
                        @click="showPassword = !showPassword"
                        x-text="showPassword ? 'Hide' : 'Show'">
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="form-checkbox">
                <span class="ml-2 text-sm text-slate-600">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                Forgot password?
            </a>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-blue-600 py-2.5 font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2">
            Sign In
        </button>
    </form>

</div>
@endsection
