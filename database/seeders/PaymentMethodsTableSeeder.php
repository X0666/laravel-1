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
            [
                'name' => 'UOB',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'BCA',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'BNI',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'BSI',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'MANDIRI',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'PERMATA',
                'account_number' => self::generateRandomAccountNumber()
            ],
            [
                'name' => 'BRI',
                'account_number' => self::generateRandomAccountNumber()
            ]
        ];

        DB::table('payment_methods')->insert($paymentMethods);
    }

    private static  function generateRandomAccountNumber() {
        $prefix = '123'; // Prefix example, change as needed
        $suffix = ''; // You can generate a random number or string for the remaining part

        // Generate random characters or numbers for the remaining part of the account number
        $remainingLength = 10 - strlen($prefix); // Adjust this length according to your requirements

        // Generate random characters or numbers for the suffix
        for ($i = 0; $i < $remainingLength; $i++) {
            $suffix .= mt_rand(0, 9); // Generates a random digit, you can adjust this according to your requirement
        }

        return $prefix . $suffix;
    }
}
