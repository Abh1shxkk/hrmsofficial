@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Profile Header --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center space-x-6">
            @if($employee?->photo)
                <img src="{{ Storage::url($employee->photo) }}" class="w-24 h-24 rounded-full object-cover border-4 border-blue-100">
            @else
                <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-3xl font-bold border-4 border-blue-50">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-500">{{ $user->email }}</p>
                @if($employee)
                    <p class="text-sm text-gray-400 mt-1">{{ $employee->designation }} -- {{ $employee->department->name ?? '' }} -- <span class="font-mono">{{ $employee->employee_code }}</span></p>
                @endif
                <span class="inline-block mt-2 px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 capitalize">
                    {{ str_replace('_', ' ', $user->role ?? 'User') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Personal Info --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Personal Information</h3>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="form-control">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="{{ $user->email }}" disabled
                           class="form-control">
                    <p class="text-xs text-gray-400 mt-1">Contact HR to change email</p>
                </div>
            </div>

            @if($employee)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                           class="form-control">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                    <input type="file" name="photo" accept="image/*"
                           class="form-file-control">
                    @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea name="address" rows="3"
                          class="form-control">{{ old('address', $employee->address) }}</textarea>
                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            @endif

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 text-sm">Save Changes</button>
            </div>
        </form>
    </div>

    @if($employee)
    {{-- Employment Info (read-only) --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Employment Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Employee Code</p>
                <p class="font-medium font-mono">{{ $employee->employee_code }}</p>
            </div>
            <div>
                <p class="text-gray-400">Department</p>
                <p class="font-medium">{{ $employee->department->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Designation</p>
                <p class="font-medium">{{ $employee->designation }}</p>
            </div>
            <div>
                <p class="text-gray-400">Joining Date</p>
                <p class="font-medium">{{ $employee->date_of_joining->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-gray-400">Employment Type</p>
                <p class="font-medium">{{ ucwords(str_replace('_', ' ', $employee->employment_type)) }}</p>
            </div>
            <div>
                <p class="text-gray-400">Status</p>
                @php $sc = ['active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-yellow-100 text-yellow-700', 'terminated' => 'bg-red-100 text-red-700']; @endphp
                <span class="px-2 py-1 text-xs rounded-full {{ $sc[$employee->status] ?? '' }}">{{ ucfirst($employee->status) }}</span>
            </div>
            <div>
                <p class="text-gray-400">Date of Birth</p>
                <p class="font-medium">{{ $employee->date_of_birth->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-gray-400">Aadhar Number</p>
                <p class="font-medium">{{ $employee->aadhar_number ?: 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-400">PAN Number</p>
                <p class="font-medium">{{ $employee->pan_number ?: 'N/A' }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Change Password --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Change Password</h3>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4 max-w-md">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="form-control">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="password" required
                       class="form-control">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="form-control">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 text-sm">Change Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
