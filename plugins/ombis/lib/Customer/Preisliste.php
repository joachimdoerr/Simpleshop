<?php

/**
 * This file is part of the Kreatif\Project package.
 *
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 12.01.21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfREDAXO\Simpleshop\Ombis\Customer;


use FriendsOfREDAXO\Simpleshop\Ombis\Api;
use Kreatif\WSConnectorException;


class Preisliste
{

    public static function getAll($fields = [])
    {
        $response = [];
        try {
            $response = (array)Api::curl('/preisliste/', ['order' => 'DisplayName', 'filter' => 'eq(Web,1)'], 'GET', $fields)['Data'];
        } catch (WSConnectorException $ex) {
        }
        return $response;
    }
}