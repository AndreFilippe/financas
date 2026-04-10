<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@financas.com')->first();
        if (!$user) {
            User::create([
                'name' => 'Casal Administrador',
                'email' => 'admin@financas.com',
                'password' => bcrypt('admin123'),
            ]);
        }

        $this->call([
            CategorySeeder::class,
        ]);
    }
}
