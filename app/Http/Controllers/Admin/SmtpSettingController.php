<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use App\Services\SmtpConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SmtpSettingController extends Controller
{
    public function edit()
    {
        $smtpSetting = SmtpSetting::current();

        return view('settings.smtp', compact('smtpSetting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'nullable|email|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'required|in:tls,ssl,none',
            'from_address' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
        ]);

        $smtpSetting = SmtpSetting::current();

        $data = [
            'is_enabled' => $request->boolean('is_enabled'),
            'mailer' => 'smtp',
            'host' => $validated['host'],
            'port' => $validated['port'],
            'username' => $validated['username'],
            'encryption' => $validated['encryption'],
            'from_address' => $validated['from_address'],
            'from_name' => $validated['from_name'],
        ];

        if ($request->filled('password')) {
            $data['password'] = $validated['password'];
        }

        $smtpSetting->update($data);

        return back()->with('success', 'SMTP settings updated.');
    }

    public function sendTest(Request $request, SmtpConfigurator $smtpConfigurator)
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        $smtpSetting = SmtpSetting::current();

        if (! $smtpConfigurator->apply($smtpSetting)) {
            return back()
                ->withErrors(['test_email' => 'SMTP is currently disabled. Tick "Enable SMTP for password reset emails" and save settings before sending a test email.'])
                ->withInput();
        }

        try {
            Mail::raw(
                'This is a test email from your HRMS SMTP settings. If you received this email, SMTP is working.',
                function ($message) use ($validated) {
                    $message
                        ->to($validated['test_email'])
                        ->subject('HRMS SMTP Test Email');
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['test_email' => 'Test email could not be sent. Please verify SMTP host, port, encryption, username and app password.'])
                ->withInput();
        }

        return back()->with('success', 'Test email sent successfully.');
    }
}
