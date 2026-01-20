<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // This seeder is intentionally left minimal for multi-tenant applications.
        // Use the company registration UI at /register-company to create tenants and users.
        // Or create programmatically using TenantService:
        //
        // $service = app(\App\Services\TenantService::class);
        // $result = $service->createTenantWithOwner([
        //     'company_name' => 'Demo Company',
        //     'owner_name' => 'Demo User',
        //     'owner_email' => 'demo@example.com',
        //     'password' => 'password',
        //     'timezone' => 'UTC',
        // ]);
    }
}
