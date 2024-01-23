<?php

namespace App\Stripe;

use App\Models\User;
use Illuminate\Support\Str;
use Stripe\Coupon;
use Stripe\Plan as StripePlans;
use Stripe\Stripe;

class PlansManager
{
    public static function forbiddenRoute($route_name)
    {
        return in_array($route_name, [
            'hunter.page',
            'tracker.page',
            'save.asin',
            'delete.asin.track',
            'check.local.sales.history',
            'save.asin.keywords',
            'check.asin.keywords.position',
            'delete.asin.keywords',
            'keyword.cloud.page'
        ]);
    }

    /**
     * @param $plan
     * @return bool
     */
    public static function isProPlan($plan): bool
    {
        return !Str::contains($plan, 'newbie');
    }

    public function getPlans()
    {
        return [
            ['stripe_price' => 'monthly', 'price' => 999, 'name' => 'monthly', 'id' => env('STRIPE_MONTHLY_PRICE_ID')],
            ['stripe_price' => 'yearly', 'price' => 9999, 'name' => 'yearly', 'id' => env('STRIPE_YEARLY_PRICE_ID')],
        ];
    }

    public function getIdByStripePrice($plan_id)
    {
        $planArray = $this->getPlans();
        foreach ($planArray as $plan) {
            if ($plan['stripe_price'] === $plan_id) {
                return $plan['id'];
            }
        }
        return null; // Return null if no matching stripe_price is found
    }

    public function getPromo($index)
    {
        $promotions = [
            1 => ['mon-pro-spec' => 'Monthly Pro - $9.99'],
            2 => ['six-mon-pro' => 'Six Month Pro - $49.99'],
            3 => ['y-pro-sale' => 'Yearly Pro - $89.99'],
            4 => ['six-pro-disc' => 'Six Month Pro - $39.90'],
            5 => ['y-pro-spec' => 'Yearly Pro - $99.90'],
            6 => ['mon-pro-v2' => 'Monthly Pro - $19.99'],
            7 => ['monthly-newbie' => 'Monthly Newbie - $9.99'],
            8 => ['mon-pro-1d' => 'Monthly Pro - $1.00'],
            9 => ['six-mon-pro2' => 'Six Month Pro - $59.99']
        ];
        return isset($promotions[$index]) ? $promotions[$index] : $promotions[1];
    }

    public function getCoupon($coupon_id)
    {
        $coupons = [
            1 => 'proadvantage'
        ];

        if (!isset($coupons[$coupon_id])) {
            return null;
        }

        return $coupons[$coupon_id];
    }

    public function planExists($plan_id)
    {
        $planArray = $this->getPlans();
        foreach ($planArray as $plan) {
            if ($plan['stripe_price'] === $plan_id) {
                return true;
            }
        }
        return false;
    }

    public function parsePlanName($plan_name)
    {
        switch ($plan_name) {
            case 'mon-pro-spec':
            case 'mon-pro-v2':
            case 'mon-pro-1d':
                return 'monthly-pro';
            case 'six-mon-pro':
            case 'six-mon-pro2':
            case 'six-pro-disc':
                return 'six-month-pro';
            case 'y-pro-spec':
                return 'yearly-pro-special';
            case 'y-pro-sale':
                return 'yearly-pro-sale';
            case 'three-mon-spec':
                return 'three-month-special';
            case 'y-pro-new':
                return 'yearly-pro';
            case 'y-pro-disc':
                return 'yearly-newbie-special';
            default:
                return $plan_name;
        }
    }

    public function getPlanPrice($plan, Coupon $coupon = null)
    {
        $plans = [
            env('STRIPE_MONTHLY_PRICE_ID') => '9.99',
            env('STRIPE_YEARLY_PRICE_ID') => '99.99',

        ];

        $price = $plans[$plan];

        if (!is_null($coupon)) {
            if ($coupon->amount_off) {
                $price = $price - $coupon->amount_off / 100;
            }

            if ($coupon->percent_off) {
                $price = ($price - ($price * $coupon->percent_off / 100));
            }
        }

        return $price;
    }
}
