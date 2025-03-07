<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {

        // Ambil user pertama
        $users = User::first();
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345'),
            'auth' => 'Admin',
            'status' => 'Active',
            'nama_cabang' => 'Cabang Utama',
            'alamat_cabang' => 'Jl. Utama No. 1',
            'alamat' => 'Jl. Admin No. 99',
            'no_telp' => '081918408597',
            'email_verified_at' => now(),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->assignRole('Admin');
    }

    
}
