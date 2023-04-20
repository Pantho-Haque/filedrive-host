<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Folder;
use App\Models\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

// seed a particular useer with user id
        // $user = User::find(5);

        // if ($user) {
        //     Folder::factory()
        //         ->count(3)
        //         ->has(File::factory()->count(7))
        //         ->create(['user_id' => $user->id]);
        // }




        User::factory()
            ->count(3)
            ->has(Folder::factory()->count(2)
                ->has(File::factory()->count(9))
            )
            ->create();
        
        User::factory()
            ->count(2)
            ->has(Folder::factory()->count(4)
                ->has(File::factory()->count(3))
            )
            ->create();
        User::factory()
            ->count(5)
            ->has(Folder::factory()->count(7)
                ->has(File::factory()->count(4))
            )
            ->create();

        DB::table("users")->insert([
            'name' => "Pantho Haque",
            'email' => "pantho@gmail.com",
            'password' => Hash::make('pantho'),
            'profile_pic' => "public/backendfiles/admin/profilepic.jpg",
            'used_storage' => 0.0,
            'email_verified_at'=>now(),
            'isAdmin'=>true,
        ]);

// 

        // $faker = Faker::create();
        // foreach (range(1,5) as $value) {
            // DB::table("users")->insert([
            //     'name' => $faker->name(),
            //     'email' => $faker->unique()->safeEmail(),
            //     'password' => Hash::make('password'),
            //     'used_storage' => $faker->numberBetween(0, 50),
            // ]);
        // }
    }

}
