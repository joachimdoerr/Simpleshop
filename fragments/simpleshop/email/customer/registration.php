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

$email    = $this->getVar('email');
$addText  = $this->getVar('additional_text', '');
$password = $this->getVar('password');
$url      = trim($this->getVar('url'));

if ($url == '') {
    $shopSettings = \rex::getConfig('simpleshop.Settings');
    $accountPage  = $shopSettings['linklist']['dashboard'] ? \rex_article::get($shopSettings['linklist']['dashboard']) : null;
    $url          = $accountPage ? $accountPage->getUrl() : '';
}

$url_label = $this->getVar('url_label', $url);

?>
<p>###label.email__registration_text###</p>

<table style="width:100%;">
    <?php if (strlen($url)): ?>
        <tr>
            <td>###label.website###</td>
            <td><a href="<?= $url ?>"><?= $url_label ?></a></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td>###label.email###</td>
        <td><?= $email ?></td>
    </tr>
    <tr>
        <td>###label.password###</td>
        <td><?= $password ?></td>
    </tr>
</table>

<?php if (strlen($addText)): ?>
    <p><?= $addText ?></p>
<?php endif; ?>
