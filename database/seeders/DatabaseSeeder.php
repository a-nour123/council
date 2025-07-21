<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\User;
use Illuminate\Database\DBAL\TimestampType;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        /*
        Note : you can login with email and password = "password"
        */

        $this->call(ProjectObjectivesSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PositionSeeder::class);
        $this->call(AcadimicRankSeeder::class);
        $this->call(YearlyCalendarSeeder::class);
        $this->call(HeadquarterSeeder::class);
        $this->call(FacultySeeder::class);
        $this->call(FacultyHeadquarterSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(FacultyCouncilSeeder::class);
        $this->call(Department_CouncilSeeder::class);
        $this->call(AxisSeeder::class);
        $this->call(ClassificationDecisionsSeeder::class);
        
        // $this->call(TopicSeeder::class);
        // $this->call(TopicAxisSeeder::class);




    }
}
