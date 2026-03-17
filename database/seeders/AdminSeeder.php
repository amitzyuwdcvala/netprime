<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'flixygo@admin.com',
            'password' => Hash::make('Flixygo@2026'),
            'created_by' => 0, // Super Admin
            'is_active' => true,
            'lang' => 'en',
        ]);
    }
}
