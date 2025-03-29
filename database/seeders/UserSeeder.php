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
            'email' => 'lombokpdhas@gmail.com',
            'password' => Hash::make('12345'),
            'auth' => 'Admin',
            'status' => 'Active',
            'nama_cabang' => 'Cabang Utama',
            'alamat_cabang' => 'Lombok, Nusa Tenggara Barat, Indonesia',
            'no_telp' => '081122334455',
            'email_verified_at' => now(),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->assignRole('Admin');
    }

    
}
