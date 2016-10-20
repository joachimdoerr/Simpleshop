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

use Symfony\Component\Yaml\Exception\RuntimeException;

class Payment extends Plugin
{
    protected static $type = 'payment';
}


abstract class PaymentAbstract extends PluginAbstract
{
    public $plugin_name;

    public abstract function getPrice();
    public abstract function getName();

    public static function get()
    {
        return Payment::getByClass(get_called_class());
    }

    public function getData()
    {

    }
}