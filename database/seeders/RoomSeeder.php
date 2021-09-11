<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Room::create([
            'name' => 'general',
            'code' => simple_two_way_crypt(1)
        ]);

        Room::create([
            'name' => 'others',
            'code' => simple_two_way_crypt(2)
        ]);
    }
}
