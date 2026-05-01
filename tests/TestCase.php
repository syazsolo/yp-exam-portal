<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    protected function createAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function createStudent(): User
    {
        return User::factory()->student()->create();
    }

    protected function createLecturer(): User
    {
        return User::factory()->lecturer()->create();
    }
}
