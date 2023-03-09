<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(!User::where('name','=','super')->exists()) {
            $superAdmin = User::create([
                'name' => 'super',
                'password' => bcrypt('super'),
                'email' => 'super@super.com',
                'photo' => "none",
                'phone' => "555",
                'full_name' => "super admin!"
            ]);
            $superAdmin->assignRole('super-admin');
            $superAdmin->save();
        }

        if(!User::where('name','=','admin')->exists()) {
            $admin = User::create([
                'name' => 'admin',
                'password' => bcrypt('admin'),
                'email' => 'admin@admin.com',
                'photo' => "none",
                'phone' => "666",
                'full_name' => "admin!"
            ]);
            $admin->assignRole('admin');
            $admin->save();
        }
    }
}
