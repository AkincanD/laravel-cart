<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponTrait;

/**
 * Class Fixed.
 */
class Fixed implements CouponContract
{
    use CouponTrait;

    public $code;
    public $value;

    /**
     * Fixed constructor.
     *
     * @param $code
     * @param $value
     * @param array $options
     */
    public function __construct($code, $value, $options = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setOptions($options);
    }

    /**
     * Gets the discount amount.
     *
     * @param $throwErrors boolean this allows us to capture errors in our code if we wish,
     * that way we can spit out why the coupon has failed
     *
     * @return string
     */
    public function discount($throwErrors = false)
    {
        $subTotal = app(LaraCart::SERVICE)->subTotal(false);

        if (config('laracart.tax_item_before_discount')) {
            $subTotal = $subTotal + app(LaraCart::SERVICE)->taxTotal(false, true, true, false);
        }

        if (config('laracart.discountOnFees', false)) {
            $subTotal = $subTotal + app(LaraCart::SERVICE)->feeTotals(false);
        }

        $total = $subTotal - $this->value;

        if ($total < 0) {
            return $subTotal;
        }

        return $this->value;
    }

    /**
     * If an item is supplied it will get its discount value.
     *
     * @param CartItem $item
     *
     * @return float
     */
    public function forItem(CartItem $item)
    {
        if (config('laracart.tax_item_before_discount')) {
            return $item->subTotal(false, false, false, true) * $this->value;
        }

        return $this->value;
    }

    /**
     * Displays the value in a money format.
     *
     * @param null $locale
     * @param null $currencyCode
     *
     * @return string
     */
    public function displayValue($locale = null, $currencyCode = null, $format = true)
    {
        return LaraCart::formatMoney(
            $this->discount(),
            $locale,
            $currencyCode,
            $format
        );
    }
}
