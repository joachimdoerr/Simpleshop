<?php

/**
 * This file is part of the Simpleshop package.
 *
 * @author FriendsOfREDAXO
 * @author a.platter@kreatif.it
 * @author jan.kristinus@yakamara.de
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfREDAXO\Simpleshop;

class DiscountGroup extends Discount
{
    const TABLE = 'rex_shop_discount_group';

    public static function ext_calculateDocument($params)
    {
        $Order          = $params->getSubject();
        $order_total    = $Order->getValue('total');
        $order_quantity = $Order->getValue('quantity');
        $discounts      = self::query()->where('status', 1)->find();

        foreach ($discounts as $discount)
        {
            $price  = $discount->getValue('price');
            $amount = $discount->getValue('amount');

            if ($order_total >= $price || ($amount && $order_quantity >= $amount))
            {
                $discount_id                            = $discount->getValue('id');
                $promotions                             = $Order->getValue('promotions');
                $promotions['discount_' . $discount_id] = $discount;
                $Order->setValue('promotions', $promotions);
                break; // discount found - stop here
            }
        }
        return $Order;
    }

//    public function applyToOrder($Order)
//    {
//        $order_total    = (float) $Order->getValue('total');
//        $order_quantity = (int) $Order->getValue('quantity');
//        $price          = (float) $this->getValue('price');
//        $amount         = (int) $this->getValue('amount');
//
//        // do some checks
//        if ($order_total < $price && (!$amount || $order_quantity < $amount))
//        {
//            throw new DiscountGroupException('Not applyable anymore', 1);
//        }
//        return parent::applyToOrder($Order);
//    }
}

class DiscountGroupException extends \Exception
{
    public function getLabelByCode()
    {
        switch ($this->getCode())
        {
            case 1:
                $errors = '###shop.error.discountgroup_not_applyable_anymore###';
                break;
            default:
                $errors = $this->getMessage();
                break;
        }
        return $errors;
    }
}