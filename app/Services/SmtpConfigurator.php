<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SmtpConfigurator
{
    public function apply(?SmtpSetting $setting = null): bool
    {
        $setting ??= SmtpSetting::first();

        if (! $setting || ! $setting->is_enabled) {
            return false;
        }

        Config::set('mail.default', $setting->mailer);
        $scheme = $setting->encryption === 'ssl' ? 'smtps' : null;

        Config::set("mail.mailers.{$setting->mailer}", [
            'transport' => 'smtp',
            'scheme' => $scheme,
            'host' => $setting->host,
            'port' => $setting->port,
            'username' => $setting->username,
            'password' => $setting->password,
            'encryption' => $setting->encryption === 'none' ? null : $setting->encryption,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        if ($setting->from_address) {
            Config::set('mail.from.address', $setting->from_address);
        }

        if ($setting->from_name) {
            Config::set('mail.from.name', $setting->from_name);
        }

        if (method_exists(Mail::getFacadeRoot(), 'purge')) {
            Mail::purge($setting->mailer);
        }

        return true;
    }
}
