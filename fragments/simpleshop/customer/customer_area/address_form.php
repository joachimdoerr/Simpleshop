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


$fields           = $this->getVar('excluded_fields', []);
$form             = $this->getVar('Form', null);
$redirect_url     = $this->getVar('redirect_url', '');
$back_url         = $this->getVar('back_url', '');
$show_save_btn    = $this->getVar('show_save_btn', true);
$real_field_names = $this->getVar('real_field_names', false);
$only_fields      = $this->getVar('only_fields', false);
$btn_label        = $this->getVar('btn_label', ucfirst(\Wildcard::get('action.save')));
$Address          = $this->getVar('Address', CustomerAddress::create());
$Customer         = Customer::getCurrentUser();


$id  = 'form-data-' . \rex_article::getCurrentId();
$sid = "form-{$id}";

if ($Customer->getValue('ctype') != 'company') {
    $fields = array_merge($fields, ['company_name']);
}

$form = $Address->getForm($form, $fields, $Customer->getId());

$form->setObjectparams('debug', false);
$form->setObjectparams('submit_btn_show', false);
$form->setObjectparams('real_field_names', $real_field_names);
$form->setObjectparams('form_ytemplate', 'custom,foundation,bootstrap');
$form->setObjectparams('error_class', 'form-warning');
$form->setObjectparams('form_showformafterupdate', true);
$form->setObjectparams('getdata', $Address->getId() > 0);
$form->setObjectparams('form_anchor', '-' . $sid);
$form->setObjectparams('form_name', $sid);
$form->setObjectparams('form_class', 'row medium-up-2');
$form->setObjectparams('form_action', '');
$form->setObjectparams('only_fields', $only_fields);

if ($redirect_url) {
    $form->setActionField('redirect', [$redirect_url]);
}

if ($show_save_btn) {
    // Submit
    $form->setValueField('html', ['', '<div class="row"><div class="column margin-small-top"><div class="column">']);

    if ($back_url) {
        $form->setValueField('html', ['', '<a href="' . $back_url . '" class="button">###action.go_back###</a>']);
    }

    $form->setValueField('submit', [
        'name'        => 'submit-' . $sid,
        'no_db'       => 'no_db',
        'labels'      => $btn_label,
        'css_classes' => 'button secondary float-right',
    ]);
    $form->setValueField('html', ['', '</div></div></div>']);
}

$formOutput = $Address->executeForm($form);

?>
<div class="account-data margin-small-top margin-bottom">
    <?= $formOutput ?>
</div>
