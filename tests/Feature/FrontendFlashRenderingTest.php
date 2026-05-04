<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FrontendFlashRenderingTest extends TestCase
{
    #[Test]
    public function authenticated_layout_is_the_only_frontend_flash_renderer(): void
    {
        $flashRenderers = collect($this->vueFilesUsingFlashProps())
            ->map(fn (string $path) => str_replace(base_path().DIRECTORY_SEPARATOR, '', $path))
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            ['resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Layouts'.DIRECTORY_SEPARATOR.'AuthenticatedLayout.vue'],
            $flashRenderers,
            'Flash messages should render through AuthenticatedLayout only, not individual pages.'
        );
    }

    #[Test]
    public function authenticated_layout_renders_success_and_error_flash_messages(): void
    {
        $layout = file_get_contents(resource_path('js/Layouts/AuthenticatedLayout.vue'));

        $this->assertStringContainsString('page.props.flash?.success', $layout);
        $this->assertStringContainsString('page.props.flash.success', $layout);
        $this->assertStringContainsString('page.props.flash?.error', $layout);
        $this->assertStringContainsString('page.props.flash.error', $layout);
    }

    /**
     * @return array<int, string>
     */
    private function vueFilesUsingFlashProps(): array
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('js')));
        $matches = [];

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'vue') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'page.props.flash') || str_contains($contents, '$page.props.flash')) {
                $matches[] = $file->getPathname();
            }
        }

        return $matches;
    }
}
