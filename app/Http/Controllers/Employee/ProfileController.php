<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $employee = $user->employee;
        $employee?->load('department');

        return view('profile.show', compact('user', 'employee'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user->update(['name' => $request->name]);

        if ($employee) {
            $data = $request->only('phone', 'address');

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
            }

            $employee->update($data);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
