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

$label_name  = sprogfield('name');
$product     = $this->getVar('product');
$image       = $product->getValue('image');
$price       = $product->getPrice(TRUE);
$features    = $product->getValue('features');
$quantity    = $product->getValue('cart_quantity');
$key         = $product->getValue('key');
$product_url = $product->getUrl();
$Category    = Category::get($product->getValue('category_id'));

if (strlen($image) == 0 && strlen($product->getValue('gallery')))
{
    $gallery = explode(',', $product->getValue('gallery'));
    $image   = $gallery[0];
}
?>
<tr class="cart-item">
    <td>
        <a href="<?= $product_url ?>"><?= Utils::getImageTag($image, 'cart-list-element-main') ?></a>
    </td>
    <td>
        <h3><?= $product->getValue($label_name) ?></h3>
        <p><?= $Category->getValue($label_name) ?></p>

        <?php foreach ($features as $feature): ?>
            <div><i class="medium-bold"><?= $feature->getValue($label_name) ?></i></div>
        <?php endforeach; ?>
    </td>
    <td>&euro; <?= format_price($price) ?></td>
    <td>
        <?php
        $this->setVar('has_quantity_control', TRUE);
        $this->setVar('has_refresh_button', TRUE);
        $this->setVar('cart-quantity', $quantity);
        $this->setVar('product_key', $key);
        echo $this->subfragment('simpleshop/product/general/cart/button.php');
        ?>
    </td>
    <td>&euro; <?= format_price($price * $quantity) ?></td>
    <td>
        <a href="<?= rex_getUrl(null, null, ['func' => 'remove', 'key' => $key]) ?>" class="remove text-center">X</a>
    </td>
</tr>