<?php

namespace Database\Seeders;

use App\Models\ActionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('action_statuses')->insert([[
            'name' => "start",
        ],
        [
            'name' => "wait"
        ],
        [
            'name' => "stop"
        ],
        [
            'name' => "pause"
        ],
        [
            'name' => "finish"
        ]]);
    }
}
