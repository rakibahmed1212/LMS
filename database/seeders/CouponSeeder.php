<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => Coupon::TYPE_PERCENT,
                'value' => 10,
                'max_uses' => 500,
                'is_active' => true,
            ],
        );

        Coupon::firstOrCreate(
            ['code' => 'FLAT5'],
            ['type' => Coupon::TYPE_FIXED, 'value' => 5, 'is_active' => true],
        );
    }
}
