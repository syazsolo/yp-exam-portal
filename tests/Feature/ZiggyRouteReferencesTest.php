<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ZiggyRouteReferencesTest extends TestCase
{
    #[Test]
    public function vue_route_references_exist_in_laravel_router(): void
    {
        $routeNames = collect($this->extractRouteNames(resource_path('js')))
            ->unique()
            ->sort()
            ->values();

        $missing = $routeNames
            ->reject(fn (string $name) => Route::has($name))
            ->values();

        $this->assertSame(
            [],
            $missing->all(),
            'These frontend route(...) references are missing from Laravel routes: '.$missing->implode(', ')
        );
    }

    /**
     * @return array<int, string>
     */
    private function extractRouteNames(string $path): array
    {
        $names = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file->isFile() || ! in_array($file->getExtension(), ['js', 'vue'], true)) {
                continue;
            }

            preg_match_all(
                '/\broute\s*\(\s*[\'"]([^\'"]+)[\'"]/',
                file_get_contents($file->getPathname()),
                $matches
            );

            array_push($names, ...$matches[1]);
        }

        return $names;
    }
}
