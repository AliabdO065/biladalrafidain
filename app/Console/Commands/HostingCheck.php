<?php
namespace App\Console\Commands;

use App\Services\WhatsAppMessaging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HostingCheck extends Command
{
    protected $signature = 'hosting:check {--production : Also require production security settings}';
    protected $description = 'Read-only hosting readiness checks; does not display credentials.';

    public function handle(WhatsAppMessaging $whatsapp): int
    {
        $checks = [];
        $add = function (string $name, bool $ok) use (&$checks) { $checks[] = [$name, $ok ? 'PASS' : 'FAIL']; };
        $add('PHP 8.2+', version_compare(PHP_VERSION, '8.2.0', '>='));
        foreach (['bcmath','dom','fileinfo','gd','mbstring','openssl','pdo','zip'] as $extension) $add('PHP '.$extension, extension_loaded($extension));
        $add('GD FreeType + WebP', function_exists('imagettftext') && function_exists('imagewebp'));
        $add('Arabic document font', is_readable(resource_path('fonts/NotoNaskhArabic-Regular.ttf')));
        $add('Latin document font', is_readable(resource_path('fonts/NotoSans-Regular.ttf')));
        $add('Correct public assets directory', is_file(public_path('fronted/landing/css/marketplace.css')) && is_file(public_path('dashboard/assets/stylesheets/workspace.css')));
        $add('Writable storage', is_writable(storage_path()));
        $add('Writable bootstrap/cache', is_writable(base_path('bootstrap/cache')));
        $key = (string) config('app.key');
        $key = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $add('Valid application encryption key', is_string($key) && \Illuminate\Encryption\Encrypter::supported($key, config('app.cipher')));
        try {
            DB::select('SELECT 1');
            $add('Database connection', true);
            $migrator = app('migrator');
            $repository = $migrator->getRepository();
            $ran = $repository->repositoryExists() ? $repository->getRan() : [];
            $add('All application migrations applied', count(array_diff(array_keys($migrator->getMigrationFiles(database_path('migrations'))), $ran)) === 0);
        } catch (\Throwable $error) {
            $add('Database connection / migrations', false);
        }
        if ($this->option('production')) {
            $add('Production environment', app()->environment('production'));
            $add('Debug disabled', !config('app.debug'));
            $url = parse_url((string)config('app.url'));
            $add('HTTPS public URL', ($url['scheme'] ?? '') === 'https' && filled($url['host'] ?? null) && !in_array($url['host'], ['localhost','127.0.0.1','example.com'], true));
            $add('Secure session cookie', config('session.secure') === true);
            $add('HTTP-only session cookie', config('session.http_only') === true);
            $add('Shared hosting renderer', config('hosting.mode') !== 'shared' || config('documents.renderer') === 'gd');
        }
        $this->table(['Check', 'Result'], $checks);
        $this->line('WhatsApp: '.($whatsapp->configured() ? 'credentials present; provider acceptance still requires verification' : 'not connected'));
        $this->line('Last successful hosting tick: '.(Cache::get('hosting:last_tick') ?: 'not recorded'));
        $this->line('Still verify in cPanel: public document root, HTTPS redirect, cron execution, backups and a real delivery test.');
        return collect($checks)->contains(fn ($check) => $check[1] === 'FAIL') ? self::FAILURE : self::SUCCESS;
    }
}
