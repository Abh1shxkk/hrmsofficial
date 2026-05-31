@extends('layouts.app')
@section('title', 'SMTP Settings')

@section('content')
<div class="max-w-3xl space-y-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">SMTP Settings</h2>
        <p class="mt-1 text-sm text-gray-500">Configure outgoing email for password reset links.</p>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        For Gmail, use host <strong>smtp.gmail.com</strong>, port <strong>587</strong>, encryption <strong>TLS</strong>, and a Google App Password. Your normal Google account password will not work when 2-step verification is enabled.
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('settings.smtp.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                Current status:
                @if($smtpSetting->is_enabled)
                    <span class="font-semibold text-green-700">Enabled</span>
                @else
                    <span class="font-semibold text-red-700">Disabled</span>
                @endif
                <span class="mx-2 text-gray-300">|</span>
                Password:
                @if($smtpSetting->password)
                    <span class="font-semibold text-green-700">Saved</span>
                @else
                    <span class="font-semibold text-red-700">Not saved</span>
                @endif
            </div>

            <input type="hidden" name="is_enabled" value="0">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_enabled" value="1" class="form-checkbox" @checked(old('is_enabled', $smtpSetting->is_enabled))>
                <span class="text-sm font-medium text-gray-700">Enable SMTP for password reset emails</span>
            </label>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">SMTP Host *</label>
                    <input type="text" name="host" value="{{ old('host', $smtpSetting->host) }}" required class="form-control" placeholder="smtp.gmail.com">
                    @error('host')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Port *</label>
                    <input type="number" name="port" value="{{ old('port', $smtpSetting->port) }}" required class="form-control" placeholder="587">
                    @error('port')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">SMTP Username</label>
                    <input type="email" name="username" value="{{ old('username', $smtpSetting->username) }}" class="form-control" placeholder="your-email@gmail.com">
                    @error('username')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">SMTP Password</label>
                    <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="{{ $smtpSetting->password ? 'Leave blank to keep current password' : 'Google app password' }}">
                    <p class="mt-1 text-xs text-gray-500">{{ $smtpSetting->password ? 'Password is saved. Leave blank to keep existing password.' : 'Enter a Google App Password before enabling SMTP.' }}</p>
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Encryption *</label>
                <select name="encryption" required class="form-control">
                    <option value="tls" {{ old('encryption', $smtpSetting->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ old('encryption', $smtpSetting->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ old('encryption', $smtpSetting->encryption) === 'none' ? 'selected' : '' }}>None</option>
                </select>
                @error('encryption')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">From Email *</label>
                    <input type="email" name="from_address" value="{{ old('from_address', $smtpSetting->from_address) }}" required class="form-control" placeholder="your-email@gmail.com">
                    @error('from_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">From Name *</label>
                    <input type="text" name="from_name" value="{{ old('from_name', $smtpSetting->from_name) }}" required class="form-control" placeholder="{{ config('app.name') }}">
                    @error('from_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save SMTP Settings</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Send Test Mail</h3>
            <p class="mt-1 text-sm text-gray-500">This uses the saved SMTP settings above.</p>
        </div>

        <form method="POST" action="{{ route('settings.smtp.test') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-start">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Test Email *</label>
                <input type="email" name="test_email" value="{{ old('test_email') }}" required class="form-control" placeholder="recipient@example.com">
                @error('test_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="mt-6 rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700">Send Test Mail</button>
        </form>
    </div>
</div>
@endsection
