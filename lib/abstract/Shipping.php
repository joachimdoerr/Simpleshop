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

class Shipping extends Plugin
{
    protected static $type = 'shipping';
}


abstract class ShippingAbstract extends PluginAbstract
{
    public    $plugin_name;
    protected $name           = '';
    protected $hasCosts       = true;
    protected $price          = 0;
    protected $tax_percentage = 0;
    protected $parcels        = [];

    public function getName()
    {
        return $this->name;
    }

    public function hasCosts()
    {
        return $this->hasCosts;
    }

    public function usedForFrontend()
    {
        return true;
    }

    public function setParcels($parcels)
    {
        $_parcels = [];

        foreach ($parcels as $parcel) {
            if (!$parcel instanceof Parcel) {
                throw new ShippingException('Parcel must be of type Parcel');
            }
            $_parcels[] = $parcel;
        }
        $this->setValue('parcels', $_parcels);
    }

    public function getPrice($order, $products = null)
    {
        if ($order->isTaxFree()) {
            $price = $this->price;
        } else {
            $price = $this->price - $this->getTax();
        }
        return $price;
    }

    public function getNetPrice($order, $products = null)
    {
        return $this->price;
    }

    public function getTax()
    {
        return $this->price / (100 + $this->tax_percentage) * $this->tax_percentage;
    }

    public function getTaxPercentage()
    {
        return $this->tax_percentage;
    }

    public static function get()
    {
        return Shipping::getByClass(get_called_class());
    }
}

class ShippingException extends \Exception
{
}