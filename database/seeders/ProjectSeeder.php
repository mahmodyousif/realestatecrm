<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Project;
 

class ProjectSeeder extends Seeder

{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->count(50)->create();

    }
}
