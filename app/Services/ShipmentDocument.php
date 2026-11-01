<?php

namespace App\Services;

use App\Models\LandingShipment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ShipmentDocument
{
    public function binary(): ?string
    {
        $cached = glob(str_replace('\\', '/', getenv('LOCALAPPDATA') ?: '').'/ms-playwright/chromium-*/chrome-win64/chrome.exe') ?: [];
        rsort($cached, SORT_NATURAL);
        foreach (array_filter(array_merge([config('whatsapp.chrome_binary')], $cached, [
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            '/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'])) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function render(LandingShipment $shipment, string $locale = null): array
    {
        if (config('documents.renderer', 'gd') === 'gd') {
            $folder = storage_path('app/private/documents/'.Str::uuid());
            File::ensureDirectoryExists($folder, 0700);
            $originalLocale = app()->getLocale();
            $locale = ($locale ?: $originalLocale) === 'ar' ? 'ar' : 'en';
            try {
                app()->setLocale($locale);
                app(ShipmentBillImage::class)->render($shipment, $folder.'/document.png', $locale);
                return ['path' => $folder.'/document.png', 'folder' => $folder];
            } catch (\Throwable $error) {
                File::deleteDirectory($folder);
                throw $error;
            } finally {
                app()->setLocale($originalLocale);
            }
        }
        if (! $binary = $this->binary()) {
            throw new \RuntimeException('Document renderer is not configured.');
        }
        $folder = storage_path('app/private/documents/'.Str::uuid());
        File::ensureDirectoryExists($folder, 0700);
        $html = $folder.'/document.html';
        $png = $folder.'/document.png';
        $originalLocale = app()->getLocale();
        if ($locale) {
            app()->setLocale($locale === 'ar' ? 'ar' : 'en');
        }
        try {
            File::put($html, view('dashboard.documents.shipment', ['shipment' => $shipment, 'forImage' => true])->render());
        } finally {
            app()->setLocale($originalLocale);
        }
        $uri = 'file:///'.str_replace('\\', '/', $html);
        $arguments = [$binary, '--headless', '--disable-gpu', '--disable-extensions', '--disable-breakpad', '--hide-scrollbars', '--no-first-run',
            '--no-default-browser-check', '--disable-background-networking', '--force-device-scale-factor=1',
            '--user-data-dir='.$folder.'/browser', '--screenshot='.$png, '--window-size=1130,1600', '--virtual-time-budget=1500', $uri];
        if (config('whatsapp.chrome_no_sandbox')) {
            array_splice($arguments, 1, 0, ['--no-sandbox']);
        }
        $process = new Process($arguments);
        try {
            $process->setTimeout(40)->mustRun();
            if (! is_file($png) || filesize($png) === 0) {
                throw new \RuntimeException('Document rendering failed.');
            }

            return ['path' => $png, 'folder' => $folder];
        } catch (\Throwable $error) {
            File::deleteDirectory($folder);
            throw $error;
        }
    }
}
