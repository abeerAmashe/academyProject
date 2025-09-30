<?php

namespace Database\Seeders;

use App\Models\OfferStudent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfferStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       OfferStudent::factory()
    ->count(10)
    ->create();

    }
}