<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CommunityCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Course Help', 'access_role' => 'student'],
            ['name' => 'Career Guidance', 'access_role' => 'all'],
            ['name' => 'Platform Support', 'access_role' => 'all'],
            ['name' => 'Instructor Lounge', 'access_role' => 'instructor'],
            ['name' => 'Internship Tips', 'access_role' => 'company'],
            ['name' => 'General Chat', 'access_role' => 'all'],
        ];

        foreach ($categories as $category) {
            DB::table('community_categories')->updateOrInsert(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'access_role' => $category['access_role'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
