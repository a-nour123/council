<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'role.create', 'guard_name' => "web", 'created_at' => now()],
            ['name' => 'role.view', 'guard_name' => "web", 'created_at' => now()],
            ['name' => 'role.update', 'guard_name' => "web", 'created_at' => now()],
            ['name' => 'role.delete', 'guard_name' => "web", 'created_at' => now()],
        ];
        DB::table('permissions')->insert($permissions);
    }
}
