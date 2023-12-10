<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $paymentMethods = [
            ['name' => 'Credit Card'],
            ['name' => 'BCA'],
            ['name' => 'BNI'],
            ['name' => 'BSI'],
            ['name' => 'MANDIRI'],
            ['name' => 'PERMATA'],
            ['name' => 'BRI']
        ];

        DB::table('payment_methods')->insert($paymentMethods);
    }
}
