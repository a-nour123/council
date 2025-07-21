<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'ar_name' => 'المشرف الأعلى', 'guard_name' => 'web'],
            ['name' => 'System Admin', 'ar_name' => 'مسؤول النظام', 'guard_name' => 'web'],
            ['name' => 'Faculty Admin', 'ar_name' => 'مسؤول كلية', 'guard_name' => 'web'],
            ['name' => 'Member', 'ar_name' => 'عضو هيئة تدريس', 'guard_name' => 'web']
        ];
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']], // Conditions
                $role // Values to update or insert
            );
        }
    }
}
