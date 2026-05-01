<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\ViteManifestNotFoundException;
use PDOException;
use Throwable;

/**
 * Maps low-level boot / infrastructure exceptions onto a friendly
 * diagnosis page. Detection is based on exception class plus SQLSTATE
 * and driver error codes (not message strings) so it stays stable
 * across PHP and database driver versions.
 *
 * Steps are written to be appropriate in any environment — they
 * describe the underlying fault, not a developer workflow.
 */
class SetupDiagnosis
{
    private const ICON_DB = <<<'SVG'
<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<ellipse cx="12" cy="5" rx="9" ry="3"/>
<path d="M3 5v4c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
<path d="M3 9v4c0 1.66 4.03 3 9 3s9-1.34 9-3V9"/>
<path d="M3 13v4c0 1.66 4.03 3 9 3s9-1.34 9-3v-4"/>
<line x1="18" y1="18" x2="18" y2="18.01" stroke-width="3" stroke="#8b1a2c"/>
</svg>
SVG;

    private const ICON_KEY = <<<'SVG'
<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
</svg>
SVG;

    private const ICON_BUILD = <<<'SVG'
<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<polyline points="16 18 22 12 16 6"/>
<polyline points="8 6 2 12 8 18"/>
<line x1="18" y1="18" x2="18" y2="18.01" stroke-width="3" stroke="#8b1a2c"/>
</svg>
SVG;

    private const ICON_SCHEMA = <<<'SVG'
<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
<rect x="3" y="3" width="18" height="18" rx="2"/>
<line x1="3" y1="9" x2="21" y2="9"/>
<line x1="3" y1="15" x2="21" y2="15"/>
<line x1="9" y1="3" x2="9" y2="21"/>
<line x1="15" y1="9" x2="15" y2="9.01" stroke-width="3" stroke="#8b1a2c"/>
</svg>
SVG;

    public static function diagnose(Throwable $e): ?array
    {
        // Walk the cause chain — Laravel wraps PDOException in QueryException
        // and the original SQLSTATE / errorInfo lives on the inner exception.
        for ($cursor = $e; $cursor !== null; $cursor = $cursor->getPrevious()) {
            if ($result = self::match($cursor)) {
                return $result;
            }
        }

        return null;
    }

    private static function match(Throwable $e): ?array
    {
        if ($e instanceof MissingAppKeyException) {
            return self::appKey();
        }

        if ($e instanceof ViteManifestNotFoundException) {
            return self::vite();
        }

        if ($e instanceof PDOException || $e instanceof QueryException) {
            return self::matchDatabase($e);
        }

        return null;
    }

    private static function matchDatabase(Throwable $e): ?array
    {
        $sqlstate = is_string($e->getCode()) ? $e->getCode() : null;
        $driverCode = self::driverCode($e);

        // SQLSTATE class 08 = connection exception (ANSI). MySQL also reports
        // driver code 2002 inside SQLSTATE HY000 for "cannot connect".
        if ($driverCode === 2002 || ($sqlstate !== null && str_starts_with($sqlstate, '08'))) {
            return self::dbDown();
        }

        // 28000 = invalid authorisation; MySQL 1045 / Postgres 28P01.
        if ($driverCode === 1045 || $sqlstate === '28000' || $sqlstate === '28P01') {
            return self::dbAuth();
        }

        // Database does not exist: MySQL 1049 (SQLSTATE 42000) or 3D000 (ANSI).
        if ($driverCode === 1049 || $sqlstate === '3D000') {
            return self::dbMissing();
        }

        // Base table not found: MySQL 1146 (SQLSTATE 42S02), Postgres 42P01,
        // SQLite uses HY000 / driver code 1 with no portable signal.
        if ($driverCode === 1146 || $sqlstate === '42S02' || $sqlstate === '42P01') {
            return self::migrations();
        }

        return null;
    }

    private static function driverCode(Throwable $e): ?int
    {
        $info = property_exists($e, 'errorInfo') ? $e->errorInfo : null;

        if (is_array($info) && isset($info[1]) && is_int($info[1])) {
            return $info[1];
        }

        return null;
    }

    private static function host(): array
    {
        $name = config('database.default');

        return [
            'host' => config("database.connections.{$name}.host", '—'),
            'port' => config("database.connections.{$name}.port", '—'),
            'name' => config("database.connections.{$name}.database", '—'),
        ];
    }

    private static function dbDown(): array
    {
        ['host' => $host, 'port' => $port] = self::host();

        return [
            'key' => 'db-down',
            'code' => '503',
            'icon' => self::ICON_DB,
            'title' => 'Database unavailable',
            'subtitle' => "The application could not reach the database at {$host}:{$port}.",
            'cause' => 'The database server is offline, listening on a different port, or blocked by a network rule.',
            'steps' => [
                ['text' => 'Confirm the database service is running and accepting connections.'],
                ['text' => 'Verify DB_HOST and DB_PORT in your environment match the running service.'],
                ['text' => 'Test the connection.', 'cmd' => 'php artisan db:show'],
            ],
        ];
    }

    private static function dbAuth(): array
    {
        return [
            'key' => 'db-auth',
            'code' => '503',
            'icon' => self::ICON_DB,
            'title' => 'Database access denied',
            'subtitle' => 'The credentials supplied to the database were rejected.',
            'cause' => 'DB_USERNAME or DB_PASSWORD does not match an existing database user with access to this schema.',
            'steps' => [
                ['text' => 'Update DB_USERNAME and DB_PASSWORD to match a valid database user.'],
                ['text' => 'Reload the application configuration.', 'cmd' => 'php artisan config:clear'],
            ],
        ];
    }

    private static function dbMissing(): array
    {
        ['name' => $name] = self::host();

        return [
            'key' => 'db-missing',
            'code' => '503',
            'icon' => self::ICON_DB,
            'title' => "Database '{$name}' not found",
            'subtitle' => 'The database server is reachable, but the configured schema does not exist on it.',
            'cause' => 'The database referenced by DB_DATABASE has not been provisioned yet.',
            'steps' => [
                ['text' => 'Create the database on the server.', 'cmd' => "CREATE DATABASE `{$name}`;"],
                ['text' => 'Apply the schema.', 'cmd' => 'php artisan migrate'],
            ],
        ];
    }

    private static function migrations(): array
    {
        return [
            'key' => 'migrations',
            'code' => '503',
            'icon' => self::ICON_SCHEMA,
            'title' => 'Schema is incomplete',
            'subtitle' => 'A required table is missing from the database.',
            'cause' => 'Pending migrations have not been applied to this database.',
            'steps' => [
                ['text' => 'Apply the pending migrations.', 'cmd' => 'php artisan migrate'],
            ],
        ];
    }

    private static function appKey(): array
    {
        return [
            'key' => 'app-key',
            'code' => '500',
            'icon' => self::ICON_KEY,
            'title' => 'Application key not set',
            'subtitle' => 'Laravel requires an encryption key before it can boot.',
            'cause' => 'APP_KEY is empty in the current environment.',
            'steps' => [
                ['text' => 'Generate and store an application key.', 'cmd' => 'php artisan key:generate'],
                ['text' => 'In production, set APP_KEY directly in the deployed environment instead of regenerating.'],
            ],
        ];
    }

    private static function vite(): array
    {
        return [
            'key' => 'vite',
            'code' => '500',
            'icon' => self::ICON_BUILD,
            'title' => 'Frontend assets not built',
            'subtitle' => 'Laravel could not locate the Vite manifest, so JavaScript and CSS bundles cannot be served.',
            'cause' => 'The frontend has not been compiled, or the build artefacts were not deployed alongside the application.',
            'steps' => [
                ['text' => 'Install frontend dependencies.', 'cmd' => 'npm install'],
                ['text' => 'Build the production assets.', 'cmd' => 'npm run build'],
                ['text' => 'During development, run the Vite dev server instead.', 'cmd' => 'npm run dev'],
            ],
        ];
    }
}
