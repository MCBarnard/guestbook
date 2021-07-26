<?php

use Illuminate\Database\Seeder;
use App\User;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            [
                'name'=>'Admin',
                'email'=>'admin@test.com',
                'is_admin'=>'1',
                'password'=> bcrypt('1qazxsw2'),
            ],
            [
                'name'=>'User',
                'email'=>'user@test.com',
                'is_admin'=>'0',
                'password'=> bcrypt('1qazxsw2'),
            ],
            [
                'name'=>'Test',
                'email'=>'test@test.com',
                'is_admin'=>'0',
                'password'=> bcrypt('1qazxsw2'),
            ],
        ];

        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
