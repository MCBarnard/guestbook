<?php

use App\Message;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CreateMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $messages = [
            [
                'user_id' => 2,
                'from_admin' => false,
                'opened' => true,
                'message' => 'The stay was awesome, thank you for having us!',
                'created_at' => Carbon::now()->subMinutes(rand(1, 55))
            ],
            [
                'user_id' => 2,
                'from_admin' => false,
                'opened' => false,
                'message' => 'I would\'ve preferred that the bed be closer to the window but I rather enjoyed the stay.',
                'created_at' => Carbon::now()->subMinutes(rand(1, 55))
            ],
            [
                'user_id' => 3,
                'from_admin' => false,
                'opened' => false,
                'message' => 'Gives feedback on stay',
                'created_at' => Carbon::now()->subMinutes(rand(1, 55))
            ],
            [
                'user_id' => 3,
                'from_admin' => true,
                'opened' => false,
                'message' => 'Response to feedback',
                'created_at' => Carbon::now()->subMinutes(rand(1, 55))
            ],
        ];

        foreach ($messages as $key => $value) {
            Message::create($value);
        }
    }
}
