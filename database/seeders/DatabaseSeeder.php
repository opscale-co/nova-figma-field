<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\Design;
use Workbench\App\Models\User;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@laravel.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $disk = Storage::disk('public');
        $path = 'designs/sample.json';

        if (! $disk->exists($path)) {
            $disk->put($path, (string) file_get_contents(__DIR__.'/../../tests/fixtures/sample.json'));
        }

        Design::query()->firstOrCreate(
            ['name' => 'Sample design'],
            ['figma_path' => $path],
        );
    }
}
