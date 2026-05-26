<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use YellowThree\Voyager\Models\Menu;

class MenusTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        Menu::firstOrCreate([
            'name' => 'admin',
        ]);
    }
}
