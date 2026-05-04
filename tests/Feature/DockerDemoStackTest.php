<?php

namespace Tests\Feature;

use Tests\TestCase;

class DockerDemoStackTest extends TestCase
{
    public function test_demo_stack_files_are_present(): void
    {
        $this->assertFileExists(base_path('docker-compose.demo.yml'));
        $this->assertFileExists(base_path('docker/demo.Dockerfile'));
        $this->assertFileExists(base_path('docker/demo-init.sh'));
        $this->assertFileExists(base_path('docker/demo-test.sh'));
        $this->assertFileExists(base_path('docker/mysql/create-test-db.sql'));

        $this->assertFileDoesNotExist(
            base_path('docker/demo.env'),
            'docker/demo.env is replaced by compose environment as the single source of truth.'
        );
    }

    public function test_demo_stack_initializes_seeded_app_and_scheduler(): void
    {
        $compose = file_get_contents(base_path('docker-compose.demo.yml'));

        $this->assertStringContainsString('app:', $compose);
        $this->assertStringContainsString('mysql:', $compose);
        $this->assertStringContainsString('init:', $compose);
        $this->assertStringContainsString('scheduler:', $compose);
        $this->assertStringContainsString('test:', $compose);
        $this->assertStringContainsString('yp-exam-portal:demo', $compose);
        $this->assertStringContainsString('8000:8000', $compose);
        $this->assertStringContainsString('service_completed_successfully', $compose);
        $this->assertStringContainsString('php artisan schedule:work', $compose);
        $this->assertStringContainsString('sh /usr/local/bin/demo-test.sh', $compose);
        $this->assertStringContainsString('yp_exam_portal_testing', $compose);
        $this->assertStringContainsString('VITE_APP_NAME', $compose);
        $this->assertStringContainsString('healthcheck:', $compose);
        $this->assertStringContainsString('&app-key', $compose);
        $this->assertStringContainsString('*app-key', $compose);

        $initScript = file_get_contents(base_path('docker/demo-init.sh'));
        $testScript = file_get_contents(base_path('docker/demo-test.sh'));
        $testDatabaseSql = file_get_contents(base_path('docker/mysql/create-test-db.sql'));

        $this->assertStringContainsString('php artisan migrate:fresh --seed --force', $initScript);
        $this->assertStringContainsString('php artisan migrate --force', $initScript);
        $this->assertStringContainsString('SELECT COUNT(*) FROM migrations', $initScript);
        $this->assertStringContainsString('docker/mysql/create-test-db.sql', $testScript);
        $this->assertStringContainsString('php artisan test', $testScript);
        $this->assertStringContainsString('CREATE DATABASE IF NOT EXISTS yp_exam_portal_testing', $testDatabaseSql);
    }

    public function test_dockerfile_keeps_image_lean(): void
    {
        $dockerfile = file_get_contents(base_path('docker/demo.Dockerfile'));

        $this->assertStringContainsString('--virtual .build-deps', $dockerfile);
        $this->assertStringContainsString('apk del .build-deps', $dockerfile);
        $this->assertStringContainsString('rm -rf node_modules', $dockerfile);
        $this->assertStringContainsString('ARG APP_KEY', $dockerfile);
        $this->assertStringContainsString('ARG VITE_APP_NAME', $dockerfile);
        $this->assertStringNotContainsString('docker/demo.env', $dockerfile);
        $this->assertStringNotContainsString('cp docker/demo.env .env', $dockerfile);
    }

    public function test_readme_documents_the_docker_demo_command_and_seeded_login(): void
    {
        $readme = file_get_contents(base_path('README.md'));

        $this->assertStringContainsString('docker compose -f docker-compose.demo.yml up --build', $readme);
        $this->assertStringContainsString('docker compose -f docker-compose.demo.yml run --rm --build test', $readme);
        $this->assertStringContainsString('http://localhost:8000', $readme);
        $this->assertStringContainsString('admin@gmail.com', $readme);
        $this->assertStringContainsString('qwertyuiop', $readme);
        $this->assertStringContainsString('existing data is preserved', $readme);
    }
}
