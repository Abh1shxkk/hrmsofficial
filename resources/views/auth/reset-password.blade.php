@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create New Password</h1>
        <p class="text-gray-500 mt-1">Set a new password for your account.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required class="form-control">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input type="password" name="password" id="password" required class="form-control">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="form-control">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors">
            Reset Password
        </button>
    </form>
</div>
@endsection
