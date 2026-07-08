<?php

/**
 * Emergency Admin Tools — WarungMember
 *
 * Akses: http://yoursite.com/admin-tools.php
 * Password: otomatis dari DB_PASSWORD di .env
 *
 * Security: Hapus atau restrict IP di production.
 */
session_start();

function getAdminPasswordFromEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';

    if (! file_exists($envPath)) {
        return 'admin123';
    }

    $envContent = file_get_contents($envPath);

    if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches)) {
        $password = trim($matches[1], '"\'');

        return ! empty($password) ? $password : 'admin123';
    }

    return 'admin123';
}

$admin_password = getAdminPasswordFromEnv();

if (! isset($_SESSION['admin_authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

function getLaravelRoot()
{
    return dirname(__DIR__);
}

function executeCommand($command)
{
    if (! function_exists('shell_exec')) {
        return 'Error: shell_exec function is disabled on this server';
    }

    if (strpos($command, 'php ') === 0) {
        $phpPath = null;

        if (defined('PHP_BINARY') && is_executable(PHP_BINARY)) {
            $phpPath = PHP_BINARY;
        } elseif (is_executable('/usr/bin/php')) {
            $phpPath = '/usr/bin/php';
        } elseif (is_executable('/usr/local/bin/php')) {
            $phpPath = '/usr/local/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php83/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php83/root/usr/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php84/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php84/root/usr/bin/php';
        } else {
            $testResult = @shell_exec('php --version 2>/dev/null');
            if ($testResult && strpos($testResult, 'PHP') !== false) {
                $phpPath = 'php';
            }
        }

        if ($phpPath) {
            if (strpos($phpPath, '/') !== false) {
                $command = str_replace('php ', escapeshellarg($phpPath).' ', $command);
            } else {
                $command = str_replace('php ', $phpPath.' ', $command);
            }
        }
    }

    $output = shell_exec($command.' 2>&1');

    return $output ?: 'Command executed (no output)';
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision).' '.$units[$i];
}

function getDirSize($dir)
{
    if (! is_dir($dir)) {
        return 0;
    }
    $size = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $size += $file->getSize();
    }

    return $size;
}

function removeDirectory($dir)
{
    if (! is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir.'/'.$file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

$toolGroups = [
    'cache' => [
        'title' => 'Cache Management',
        'description' => 'Clear and manage application cache',
        'actions' => [
            'clear_cache' => [
                'label' => 'Clear All Cache',
                'description' => 'Clear cache, config, routes, and views',
                'variant' => 'primary',
                'commands' => [
                    'php artisan cache:clear',
                    'php artisan config:clear',
                    'php artisan route:clear',
                    'php artisan view:clear',
                ],
            ],
            'optimize_clear' => [
                'label' => 'Clear Optimization',
                'description' => 'Clear all optimized files',
                'variant' => 'secondary',
                'commands' => ['php artisan optimize:clear'],
            ],
        ],
    ],
    'optimization' => [
        'title' => 'Application Optimization',
        'description' => 'Optimize application performance',
        'actions' => [
            'optimize' => [
                'label' => 'Optimize App',
                'description' => 'Optimize routes, config, and views',
                'variant' => 'success',
                'commands' => ['php artisan optimize --no-interaction'],
            ],
            'config_cache' => [
                'label' => 'Cache Config',
                'description' => 'Cache configuration files',
                'variant' => 'secondary',
                'commands' => ['php artisan config:cache --no-interaction'],
            ],
            'route_cache' => [
                'label' => 'Cache Routes',
                'description' => 'Cache route definitions',
                'variant' => 'secondary',
                'commands' => ['php artisan route:cache --no-interaction'],
            ],
        ],
    ],
    'storage' => [
        'title' => 'Storage Management',
        'description' => 'Manage storage links and permissions',
        'actions' => [
            'storage_link' => [
                'label' => 'Create Storage Link',
                'description' => 'Create symbolic link for storage',
                'variant' => 'primary',
                'custom' => 'handleStorageLink',
            ],
            'fix_storage_permissions' => [
                'label' => 'Fix Storage Permissions',
                'description' => 'Set proper storage permissions',
                'variant' => 'warning',
                'custom' => 'handleStoragePermissions',
            ],
            'clear_logs' => [
                'label' => 'Clear Log Files',
                'description' => 'Delete all log files',
                'variant' => 'warning',
                'custom' => 'handleClearLogs',
            ],
        ],
    ],
    'database' => [
        'title' => 'Database Operations',
        'description' => 'Database migrations and seeding',
        'actions' => [
            'migrate' => [
                'label' => 'Run Migrations',
                'description' => 'Execute database migrations',
                'variant' => 'primary',
                'commands' => ['php artisan migrate --force --no-interaction'],
            ],
            'migrate_optimize' => [
                'label' => 'Migrate + Optimize',
                'description' => 'Run migrations then rebuild caches',
                'variant' => 'success',
                'commands' => [
                    'php artisan migrate --force --no-interaction',
                    'php artisan optimize --no-interaction',
                ],
            ],
            'db_seed' => [
                'label' => 'Run Database Seeder',
                'description' => 'Seed database with sample data',
                'variant' => 'success',
                'commands' => ['php artisan db:seed --force --no-interaction'],
            ],
            'migrate_fresh' => [
                'label' => 'Fresh Migration + Seed',
                'description' => 'Drop all tables, re-migrate, and seed',
                'variant' => 'destructive',
                'commands' => ['php artisan migrate:fresh --seed --force --no-interaction'],
                'confirm' => 'Ini akan menghapus semua data. Lanjutkan?',
            ],
        ],
    ],
    'maintenance' => [
        'title' => 'Maintenance Mode',
        'description' => 'Control application maintenance mode',
        'actions' => [
            'maintenance_down' => [
                'label' => 'Enable Maintenance',
                'description' => 'Put application in maintenance mode',
                'variant' => 'warning',
                'commands' => ['php artisan down --secret=warungmember-admin'],
            ],
            'maintenance_up' => [
                'label' => 'Disable Maintenance',
                'description' => 'Bring application back online',
                'variant' => 'success',
                'commands' => ['php artisan up'],
            ],
        ],
    ],
    'security' => [
        'title' => 'Security',
        'description' => 'Security and key management',
        'actions' => [
            'key_generate' => [
                'label' => 'Generate App Key',
                'description' => 'Generate new application encryption key',
                'variant' => 'destructive',
                'commands' => ['php artisan key:generate --force'],
                'confirm' => 'Ini akan generate APP_KEY baru. Lanjutkan?',
            ],
        ],
    ],
    'environment' => [
        'title' => 'Environment',
        'description' => 'Environment file management',
        'actions' => [
            'check_env' => [
                'label' => 'Check .env File',
                'description' => 'Validate environment configuration',
                'variant' => 'secondary',
                'custom' => 'handleCheckEnv',
            ],
            'show_env' => [
                'label' => 'Show .env Content',
                'description' => 'Display environment file (masked)',
                'variant' => 'secondary',
                'custom' => 'handleShowEnv',
            ],
            'backup_env' => [
                'label' => 'Backup .env File',
                'description' => 'Create backup of environment file',
                'variant' => 'warning',
                'custom' => 'handleBackupEnv',
            ],
        ],
    ],
    'diagnostics' => [
        'title' => 'System Diagnostics',
        'description' => 'System health and debugging tools',
        'actions' => [
            'health_check' => [
                'label' => 'System Health Check',
                'description' => 'Complete system health diagnosis',
                'variant' => 'primary',
                'custom' => 'handleHealthCheck',
            ],
            'debug_500_error' => [
                'label' => 'Debug 500 Error',
                'description' => 'Diagnose HTTP 500 errors',
                'variant' => 'destructive',
                'custom' => 'handleDebug500',
            ],
            'disk_space' => [
                'label' => 'Disk Space Usage',
                'description' => 'Check disk space and file sizes',
                'variant' => 'secondary',
                'custom' => 'handleDiskSpace',
            ],
        ],
    ],
];

$output = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'logout') {
        session_destroy();
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }

    try {
        $laravelRoot = getLaravelRoot();

        if (! is_dir($laravelRoot) || ! file_exists($laravelRoot.'/artisan')) {
            throw new Exception('Laravel directory not found at: '.$laravelRoot);
        }

        chdir($laravelRoot);

        $actionFound = false;
        foreach ($toolGroups as $group) {
            if (isset($group['actions'][$action])) {
                $actionConfig = $group['actions'][$action];
                $actionFound = true;

                if (isset($actionConfig['commands'])) {
                    $outputs = [];
                    foreach ($actionConfig['commands'] as $command) {
                        $outputs[] = executeCommand($command);
                    }
                    $output = implode("\n\n", $outputs);
                } elseif (isset($actionConfig['custom'])) {
                    $handler = $actionConfig['custom'];
                    if (function_exists($handler)) {
                        $output = $handler();
                    } else {
                        throw new Exception("Custom handler not found: {$handler}");
                    }
                }
                break;
            }
        }

        if (! $actionFound) {
            throw new Exception("Unknown action: {$action}");
        }
    } catch (Exception $e) {
        $error = 'Error: '.$e->getMessage();
    }
}

// === Custom handlers ===

function handleStorageLink()
{
    $laravelRoot = getLaravelRoot();
    $publicDir = __DIR__;
    $storagePath = $publicDir.'/storage';
    $storageTarget = $laravelRoot.'/storage/app/public';

    $output = "Storage Link Management:\n";
    $output .= "Public: {$publicDir}\n";
    $output .= "Target: {$storageTarget}\n";
    $output .= 'Target exists: '.(is_dir($storageTarget) ? 'Yes' : 'No')."\n";

    if (file_exists($storagePath) || is_link($storagePath)) {
        $output .= "Removing existing...\n";
        if (is_link($storagePath)) {
            unlink($storagePath);
        } elseif (is_dir($storagePath)) {
            removeDirectory($storagePath);
        } else {
            unlink($storagePath);
        }
    }

    if (is_dir($storageTarget)) {
        if (symlink($storageTarget, $storagePath)) {
            $output .= "✅ Storage link created!\n";
        } else {
            $output .= "❌ Failed — try 'php artisan storage:link'\n";
        }
    } else {
        $output .= "❌ Target directory not found\n";
    }

    return $output;
}

function handleStoragePermissions()
{
    $laravelRoot = getLaravelRoot();
    $output = "Fixing Storage Permissions:\n";
    $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
    $fixed = 0;

    foreach ($dirs as $dir) {
        $path = $laravelRoot.'/'.$dir;
        if (is_dir($path) && chmod($path, 0755)) {
            $output .= "✅ {$dir}\n";
            $fixed++;
        }
    }

    $output .= "\nFixed {$fixed} directories.\n";

    return $output;
}

function handleClearLogs()
{
    $laravelRoot = getLaravelRoot();
    $logPath = $laravelRoot.'/storage/logs';
    if (! is_dir($logPath)) {
        return 'Log directory not found';
    }

    $files = glob($logPath.'/*.log');
    $count = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $count++;
        }
    }

    return "Deleted {$count} log files.";
}

function handleCheckEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';

    $output = "Environment File Check:\n";
    $output .= '.env exists: '.(file_exists($envPath) ? 'Yes' : 'No')."\n";

    if (file_exists($envPath)) {
        $output .= 'Size: '.filesize($envPath)." bytes\n";
        $output .= 'Modified: '.date('Y-m-d H:i:s', filemtime($envPath))."\n";

        $envContent = file_get_contents($envPath);
        $requiredVars = ['APP_KEY', 'DB_CONNECTION', 'DB_DATABASE'];
        foreach ($requiredVars as $var) {
            $exists = strpos($envContent, $var.'=') !== false;
            $output .= "{$var}: ".($exists ? 'Set' : 'Missing')."\n";
        }
    }

    return $output;
}

function handleShowEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';
    if (! file_exists($envPath)) {
        return '❌ .env not found';
    }

    $envContent = file_get_contents($envPath);
    $maskedContent = preg_replace('/(APP_KEY|DB_PASSWORD|.*_SECRET|.*_TOKEN|.*_KEY)=(.+)/i', '$1=***MASKED***', $envContent);

    return "Environment File (sensitive values masked):\n\n".$maskedContent;
}

function handleBackupEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';
    $backupPath = $laravelRoot.'/.env.backup.'.date('Y-m-d_H-i-s');

    if (! file_exists($envPath)) {
        return '❌ .env not found';
    }

    if (copy($envPath, $backupPath)) {
        return '✅ Backed up to: '.basename($backupPath);
    }

    return '❌ Backup failed';
}

function handleHealthCheck()
{
    $laravelRoot = getLaravelRoot();
    $output = "System Health Check:\n\n";

    $output .= '🔧 PHP: '.PHP_VERSION."\n";
    $output .= 'Memory: '.ini_get('memory_limit')."\n";
    $output .= 'Max Execution: '.ini_get('max_execution_time')."s\n";

    $output .= "\n🔌 Extensions:\n";
    $requiredExtensions = ['pdo', 'mbstring', 'tokenizer', 'json', 'openssl', 'curl'];
    foreach ($requiredExtensions as $ext) {
        $output .= "{$ext}: ".(extension_loaded($ext) ? '✅' : '❌')."\n";
    }

    $output .= "\n📁 Files:\n";
    $files = ['artisan', 'composer.json', '.env', 'bootstrap/app.php'];
    foreach ($files as $file) {
        $output .= "{$file}: ".(file_exists($laravelRoot.'/'.$file) ? '✅' : '❌')."\n";
    }

    $output .= "\n📂 Directories:\n";
    $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
    foreach ($dirs as $dir) {
        $path = $laravelRoot.'/'.$dir;
        $exists = is_dir($path);
        $writable = $exists && is_writable($path);
        $output .= "{$dir}: ".($exists ? ($writable ? '✅ Writable' : '⚠️ Not Writable') : '❌ Missing')."\n";
    }

    return $output;
}

function handleDebug500()
{
    $output = "HTTP 500 Error Diagnostic:\n\n";
    $output .= 'PHP: '.phpversion().' (SAPI: '.php_sapi_name().")\n";

    $requiredExtensions = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'curl'];
    foreach ($requiredExtensions as $ext) {
        $output .= "{$ext}: ".(extension_loaded($ext) ? '✅' : '❌ Missing')."\n";
    }

    $output .= "\nQuick Fixes:\n";
    $output .= "1. Generate APP_KEY → 'Generate App Key'\n";
    $output .= "2. Fix Permissions → 'Fix Storage Permissions'\n";
    $output .= "3. Clear Cache → 'Clear All Cache'\n";
    $output .= "4. Check .env → 'Show .env Content'\n";

    return $output;
}

function handleDiskSpace()
{
    $laravelRoot = getLaravelRoot();
    $output = "Disk Space:\n";
    $output .= 'Laravel: '.formatBytes(getDirSize($laravelRoot))."\n";
    $output .= 'Storage: '.formatBytes(getDirSize($laravelRoot.'/storage'))."\n";

    $free = disk_free_space($laravelRoot);
    $total = disk_total_space($laravelRoot);
    $output .= "\nDisk: ".formatBytes($total - $free).' / '.formatBytes($total);
    $output .= ' ('.round((($total - $free) / $total) * 100, 2).'%)';

    return $output;
}

function showLoginForm()
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools — WarungMember</title>
    <style>
        :root {
            --bg: #f6f6f3;
            --card: #ffffff;
            --fg: #000000;
            --muted: #62625b;
            --border: #dadad3;
            --accent: #000000;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg);
            color: var(--fg);
            margin: 0; padding: 2rem;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
        }
        .login {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .login h1 { font-size: 1.5rem; margin: 0 0 0.5rem; }
        .login p { color: var(--muted); font-size: 0.875rem; margin: 0 0 1.5rem; }
        .warning {
            background: #fff3cd; border: 1px solid #ffc10733;
            color: #856404; padding: 0.75rem; border-radius: 0.5rem;
            font-size: 0.8rem; margin-bottom: 1.5rem;
        }
        label { font-size: 0.875rem; font-weight: 600; display: block; margin-bottom: 0.4rem; }
        input {
            width: 100%; padding: 0.75rem;
            border: 1px solid var(--border); border-radius: 0.5rem;
            font-size: 0.875rem; transition: border-color 0.2s;
        }
        input:focus { outline: none; border-color: var(--accent); }
        button {
            width: 100%; padding: 0.75rem; margin-top: 1rem;
            background: var(--accent); color: white;
            border: none; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600; cursor: pointer;
        }
        button:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login">
        <h1>Admin Tools</h1>
        <p>WarungMember Emergency Access</p>
        <div class="warning"><strong>⚠</strong> Hapus file ini di production!</div>
        <form method="post">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
<?php
}

$laravelRoot = getLaravelRoot();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools — WarungMember</title>
    <style>
        :root {
            --bg: #f6f6f3;
            --card: #ffffff;
            --fg: #000000;
            --muted: #62625b;
            --border: #dadad3;
            --accent: #000000;
            --destructive: #E22625;
            --success: #16a34a;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg); color: var(--fg);
            line-height: 1.6; margin: 0; padding: 1.5rem;
        }
        .container { max-width: 1100px; margin: 0 auto; }
        .header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;
        }
        .header h1 { font-size: 1.75rem; margin: 0; letter-spacing: -0.5px; }
        .header p { color: var(--muted); margin: 0.25rem 0 0; font-size: 0.875rem; }
        .info-bar {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem; background: var(--card); border: 1px solid var(--border);
            border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
        }
        .info-item { display: flex; justify-content: space-between; font-size: 0.8rem; }
        .info-label { font-weight: 600; }
        .info-value { color: var(--muted); font-family: monospace; font-size: 0.75rem; }
        .alert {
            border-radius: 0.5rem; padding: 0.75rem 1rem;
            margin-bottom: 1.25rem; font-size: 0.85rem; border: 1px solid;
        }
        .alert-warning { background: #fef9e7; border-color: #f59e0b33; color: #92400e; }
        .alert-success { background: #f0fdf4; border-color: #16a34a33; color: #166534; }
        .alert-error { background: #fef2f2; border-color: #ef444433; color: #991b1b; }
        .tools-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; }
        .tool-group {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 1rem; overflow: hidden;
        }
        .tool-group-header { padding: 1rem 1.25rem 0.75rem; border-bottom: 1px solid var(--border); }
        .tool-group-title { font-size: 1rem; font-weight: 700; margin: 0; }
        .tool-group-desc { font-size: 0.8rem; color: var(--muted); margin: 0.25rem 0 0; }
        .tool-group-content { padding: 1rem 1.25rem; }
        .tool-actions { display: flex; flex-direction: column; gap: 0.5rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            white-space: nowrap; border-radius: 999px;
            font-size: 0.8rem; font-weight: 600; padding: 0.5rem 1rem;
            border: none; cursor: pointer; width: 100%;
            transition: opacity 0.15s;
        }
        .btn:hover:not(:disabled) { opacity: 0.88; }
        .btn:disabled { opacity: 0.5; cursor: default; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-secondary { background: var(--bg); color: var(--fg); border: 1px solid var(--border); }
        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: #000; }
        .btn-destructive { background: var(--destructive); color: white; }
        .output-box {
            background: var(--bg); border: 1px solid var(--border);
            padding: 1rem; border-radius: 0.75rem; margin: 1rem 0;
            white-space: pre-wrap; font-family: monospace;
            font-size: 0.75rem; line-height: 1.5;
            max-height: 350px; overflow-y: auto;
        }
        .footer {
            margin-top: 2rem; padding: 1.25rem;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 1rem; font-size: 0.8rem; color: var(--muted);
        }
        .footer strong { color: var(--fg); }
        @media (max-width: 640px) {
            body { padding: 1rem; }
            .header { flex-direction: column; align-items: flex-start; }
            .tools-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div>
                <h1>Admin Tools</h1>
                <p>WarungMember — Emergency Access</p>
            </div>
            <form method="post" style="margin:0;">
                <button type="submit" name="action" value="logout" class="btn btn-destructive" style="width:auto;padding:0.5rem1.25rem;">Sign Out</button>
            </form>
        </header>

        <div class="info-bar">
            <div class="info-item"><span class="info-label">PHP</span><span class="info-value"><?= PHP_VERSION ?></span></div>
            <div class="info-item"><span class="info-label">Laravel Root</span><span class="info-value"><?= $laravelRoot ?></span></div>
            <div class="info-item"><span class="info-label">.env</span><span class="info-value"><?= file_exists($laravelRoot.'/.env') ? 'Exists' : 'Missing' ?></span></div>
            <div class="info-item"><span class="info-label">Storage Link</span><span class="info-value"><?= file_exists(__DIR__.'/storage') ? 'Exists' : 'Missing' ?></span></div>
        </div>

        <div class="alert alert-warning">
            <strong>⚠ Warning:</strong> Hapus file ini di production atau restrict akses!
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($output) { ?>
            <div class="alert alert-success">✅ Berhasil!</div>
            <div class="output-box"><?= htmlspecialchars($output) ?></div>
        <?php } ?>

        <div class="tools-grid">
            <?php foreach ($toolGroups as $group) { ?>
                <div class="tool-group">
                    <div class="tool-group-header">
                        <h3 class="tool-group-title"><?= htmlspecialchars($group['title']) ?></h3>
                        <p class="tool-group-desc"><?= htmlspecialchars($group['description']) ?></p>
                    </div>
                    <div class="tool-group-content">
                        <div class="tool-actions">
                            <?php foreach ($group['actions'] as $actionKey => $action) { ?>
                                <form method="post" style="margin:0;">
                                    <button
                                        type="submit" name="action"
                                        value="<?= htmlspecialchars($actionKey) ?>"
                                        class="btn btn-<?= htmlspecialchars($action['variant']) ?>"
                                        <?php if (isset($action['confirm'])) { ?>
                                        onclick="return confirm('<?= htmlspecialchars($action['confirm']) ?>')"
                                        <?php } ?>
                                        title="<?= htmlspecialchars($action['description']) ?>"
                                    ><?= htmlspecialchars($action['label']) ?></button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="footer">
            <p><strong>Emergency Access:</strong> Gunakan tools ini jika aplikasi utama tidak bisa diakses.</p>
            <p><strong>Deploy Steps:</strong> Extract zip → <code>composer install --no-dev</code> → <code>php artisan migrate --force</code> → optimize semua cache.</p>
            <p><strong>Root:</strong> <?= htmlspecialchars($laravelRoot) ?></p>
        </div>
    </div>
</body>
</html>
