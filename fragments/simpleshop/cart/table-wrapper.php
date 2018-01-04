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

$products  = $this->getVar('products', []);
$config    = array_merge([
    'class'                     => '',
    'ahead_url'                 => '',
    'email_tpl_styles'          => [],
    'has_image'                 => true,
    'has_remove_button'         => true,
    'has_refresh_button'        => false,
    'has_global_refresh_button' => true,
], $this->getVar('config', []));

$styles = array_merge([
    'body' => '',
], $config['email_tpl_styles']);

$this->setVar('config', $config);
?>
<form action="<?= rex_getUrl() ?>" method="post" class="cart-form">
    <table class="cart-content stack margin-bottom" <?= $styles['body'] ? 'style="margin-top:30px;' . $styles['body'] . '"' : '' ?>>
        <thead>
        <?= $this->subfragment('simpleshop/cart/table-head.php'); ?>
        </thead>
        <tbody>
        <?php
        foreach ($products as $product) {
            $this->setVar('product', $product);
            echo $this->subfragment('simpleshop/cart/item.php');
        }
        ?>
        </tbody>
    </table>
    <?php if (strlen($config['ahead_url'])): ?>
        <a href="<?= $config['ahead_url'] ?>" class="button ahead float-right margin-left">###action.go_ahead### &raquo;</a>
    <?php endif; ?>
    <?php if ($config['has_global_refresh_button']): ?>
        <button class="button secondary refresh float-right" type="submit" name="func" value="update">
            <i class="fa fa-refresh" aria-hidden="true"></i>&nbsp;&nbsp;
            ###action.shop_update_cart###
        </button>
    <?php endif; ?>
</form>
