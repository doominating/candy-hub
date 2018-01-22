<?php

namespace GetCandy\Api\Discounts\Criteria;

use GetCandy\Api\Discounts\Contracts\DiscountCriteriaContract;

class Coupon implements DiscountCriteriaContract
{
    public function getArea()
    {
        return 'basket';
    }

    protected $value;

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLabel()
    {
        return 'Coupon code';
    }

    public function getHandle()
    {
        return 'coupon';
    }

    public function check($user = null, $product = null, $basket = null)
    {
        if (!$basket) {
            return false;
        }
        $coupons = $basket->discounts->map(function ($item) {
            return $item->pivot->coupon;
        });

        $check = $coupons->contains($this->value);

        return $check;
    }
}
