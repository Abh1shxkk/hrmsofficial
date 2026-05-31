@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
        <p class="text-gray-500 mt-1">Enter your email to receive a reset link.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="form-control">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition-colors">
            Send Reset Link
        </button>
    </form>

    <div class="mt-5 text-center">
        <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Back to login</a>
    </div>
</div>
@endsection
