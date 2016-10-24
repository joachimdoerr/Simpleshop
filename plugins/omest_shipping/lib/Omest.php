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

class Omest extends ShippingAbstract
{
    public function getPrice()
    {
        return 5.60;
    }

    public function getName()
    {
        return '###label.standard_shipping###';
    }
}