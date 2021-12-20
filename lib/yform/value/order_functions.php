<?php

/**
 * This file is part of the Simpleshop package.
 *
 * @author FriendsOfREDAXO
 * @author a.platter@kreatif.it
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class rex_yform_value_order_functions extends rex_yform_value_abstract
{

    public function enterObject()
    {
        if (rex::isBackend() && $this->getParam('main_id')) {
            $sql              = rex_sql::factory();
            $Order            = \FriendsOfREDAXO\Simpleshop\Order::get($this->getParam('main_id'));
            $table            = $this->getParam('main_table');
            $main_id          = $this->getParam('main_id');
            $use_invoicing    = \FriendsOfREDAXO\Simpleshop\Utils::getSetting('use_invoicing', false);
            $use_packing_list = \FriendsOfREDAXO\Simpleshop\Utils::getSetting('packing_list_printing', false);
            $action           = rex_get('ss-action', 'string');

            // set customer data
            $Order->getCustomerData();
            $Customer = $Order->getValue('customer_id') ? \FriendsOfREDAXO\Simpleshop\Customer::get(
                $Order->getValue('customer_id')
            ) : $Order->getInvoiceAddress();


            if ('save-tracking-link' == $action) {
                rex_response::cleanOutputBuffers();
                $sendMail = rex_post('sendMail', 'int');
                parse_str(rex_post('formData', 'string'), $data);
                $trackingUrl = trim($data['tracking-link']);

                if ('' != $trackingUrl && filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
                    $sql = rex_sql::factory();
                    $sql->setTable(\Kreatif\Project\Order::TABLE);
                    $sql->setWhere('id = :id', ['id' => $Order->getId()]);
                    $sql->setValue('shipping_tracking_url', $trackingUrl);

                    if ($Customer && $sendMail) {
                        \Kreatif\Utils::setCLang($Customer->getValue('lang_id'));

                        $mail = new \Kreatif\Mail();
                        //$mail->addAddress($Customer->getValue('email'));
                        $mail->addAddress('a.platter@kreatif.it');
                        $mail->Subject = str_replace('{{ODER_NUMBER}}', $Order->getId(), Wildcard::get('label.order_ready_to_send'));
                        $mail->setVar('body', strtr(Wildcard::get('label.order_ready_to_send_message'), [
                            '{{TRACKING_URL}}' => "<a href='{$trackingUrl}'>{$trackingUrl}</a>",
                        ]), false);
                        $_success = $mail->send();

                        if ($_success) {
                            $sql->setValue('status', 'SH');
                            $sql->setValue('shipping_info_sent', date('Y-m-d H:i:s'));
                        }
                    }
                    $sql->update();
                    $Order = \FriendsOfREDAXO\Simpleshop\Order::query()->where('id', $Order->getId())->findOne();
                } else {
                    rex_response::sendJson(['message' => 'Bitte eine gültige Url eingeben']);
                    exit;
                }
            }


            if (strlen($table) && $this->getParam('send') == 0 && $this->getParam('main_id') > 0) {
                // set user lang id
                if ($Customer) {
                    \rex_clang::setCurrentId($Customer->getValue('lang_id', false, \rex_clang::getCurrentId()));
                    setlocale(
                        LC_ALL,
                        \rex_clang::getCurrent()->getValue('clang_setlocale')
                    );
                }

                switch ($action) {
                    case 'generate_pdf':
                        rex_response::cleanOutputBuffers();
                        $PDF = $Order->getInvoicePDF('invoice', false);
                        $PDF->Output();
                        exit;

                    case 'download_xml':
                        rex_response::cleanOutputBuffers();
                        $iDateTs = strtotime($Order->getValue('createdate'));
                        $iDate   = date('Y-m-d', $iDateTs);

                        $XMLi = $Order->getXML();

                        if ($XMLi) {
                            $XMLi->buildXML();
                            $xml = Wildcard::parse($XMLi->getXMLFormated());

                            $folder   = rex_path::addonData('simpleshop', 'invoice_xml/' . date('Y', $iDateTs) . '/' . date('m', $iDateTs));
                            $filename = rex::getServerName() . '_' . $iDate . '__' . $Order->getValue('invoice_num') . '.xml';

                            rex_dir::create($folder, true);
                            rex_file::put($folder . '/' . $filename, $xml);

                            rex_response::setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
                            rex_response::sendContent($xml, 'text/xml');
                            exit;
                        }
                        break;

                    case 'generate_packing_list':
                        rex_response::cleanOutputBuffers();
                        $PDF = $Order->getPackingListPDF(false);
                        $PDF->Output();
                        exit;

                    case 'resend_email':
                        $Controller = new \FriendsOfREDAXO\Simpleshop\CheckoutController();
                        $Controller->setOrder($Order);
                        $Controller->sendMail();

                        unset($_GET['ss-action']);
                        $_GET['ss-msg'] = $action;
                        header('Location: ' . html_entity_decode(rex_url::currentBackendPage($_GET)));
                        exit;

                    case 'show-pdf':
                        rex_response::cleanOutputBuffers();
                        $PDF = $Order->getInvoicePDF($use_invoicing ? 'invoice' : 'order');
                        $PDF->Output();
                        exit;

                    case 'generate_creditnote':
                        $CreditNote = \FriendsOfREDAXO\Simpleshop\Order::create();

                        $CreditNote->calculateCreditNote($Order);
                        $CreditNote->save();
                        $sql->commit();

                        unset($_GET['ss-action']);
                        $_GET['ss-msg'] = $action;
                        header('Location: ' . html_entity_decode(rex_url::currentBackendPage($_GET)));
                        exit;

                    case 'recalculate_sums':
                        $promotions     = $Order->getValue('promotions', false, []);
                        $order_products = $Order->getOrderProducts();

                        $Order->recalculateDocument($order_products, $promotions);
                        $Order->setValue('invoice_num', 4);
                        $Order->save();
                        $sql->commit();

                        unset($_GET['ss-action']);
                        $_GET['ss-msg'] = $action;
                        header('Location: ' . html_entity_decode(rex_url::currentBackendPage($_GET)));
                        exit;
                }
            }

            $output = [];
            $msg    = rex_get('ss-msg', 'string');

            if ($msg) {
                echo rex_view::info(rex_i18n::msg("label.msg_{$msg}"));
            }

            if (\rex_addon::get('kreatif-mpdf')->isAvailable()) {
                $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $main_id,
                            'func'       => rex_request('func', 'string'),
                            'ss-action'  => 'show-pdf',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-default" target="_blank">
                    <i class="fa fa-print"></i>&nbsp;
                    ' . rex_i18n::msg('label.show_order_pdf') . '
                </a>
            ';
            }
            if ($Customer) {
                $output[] = '
                <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $main_id,
                            'func'       => rex_request('func', 'string'),
                            'ss-action'  => 'resend_email',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-default">
                    <i class="fa fa-send"></i>&nbsp;
                    ' . rex_i18n::msg('label.resend_email') . '
                </a>
            ';
            }
            $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                    [
                        'table_name' => $table,
                        'data_id'    => $main_id,
                        'func'       => rex_request('func', 'string'),
                        'ss-action'  => 'recalculate_sums',
                        'ts'         => time(),
                    ]
                ) . '" class="btn btn-default">
                    <i class="fa fa-calculator"></i>&nbsp;
                    ' . rex_i18n::msg('label.recalculate_sums') . '
                </a>
            ';
            if ($use_packing_list) {
                $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $main_id,
                            'func'       => rex_request('func', 'string'),
                            'ss-action'  => 'generate_packing_list',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-default">
                        <i class="fa fa-ship"></i>&nbsp;
                        Lieferschein drucken
                    </a>
                ';
            }

            if ($use_invoicing) {
                $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $main_id,
                            'func'       => rex_request('func', 'string'),
                            'ss-action'  => 'generate_pdf',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-default">
                        <i class="fa fa-file"></i>&nbsp;
                        PDF drucken
                    </a>
                ';
                $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $main_id,
                            'func'       => rex_request('func', 'string'),
                            'ss-action'  => 'download_xml',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-default">
                                <i class="fa fa-code"></i>&nbsp;
                                XML downloaden
                            </a>
                        ';
            }

            if ($Order->getValue('status') == 'CA') {
                $CreditNote = \FriendsOfREDAXO\Simpleshop\Order::getOne(
                    false,
                    [
                        'filter'  => [['ref_order_id', $Order->getId()]],
                        'orderBy' => 'id',
                    ]
                );

                if ($use_invoicing) {
                    if ($CreditNote) {
                        $output[] = '
                            <a href="' . rex_url::currentBackendPage(
                                [
                                    'table_name' => $table,
                                    'data_id'    => $CreditNote->getId(),
                                    'func'       => 'edit',
                                    'ts'         => time(),
                                ]
                            ) . '" class="btn btn-primary">
                                <i class="fa fa-money"></i>&nbsp;
                                ' . rex_i18n::msg('action.goto_creditnote') . '
                            </a>
                        ';
                    } else {
                        $output[] = '
                            <a href="' . rex_url::currentBackendPage(
                                [
                                    'table_name' => $table,
                                    'data_id'    => $main_id,
                                    'func'       => rex_request('func', 'string'),
                                    'ss-action'  => 'generate_creditnote',
                                    'ts'         => time(),
                                ]
                            ) . '" class="btn btn-default">
                                <i class="fa fa-money"></i>&nbsp;
                                ' . rex_i18n::msg('label.generate_creditnote') . '
                            </a>
                        ';
                    }
                }
            } elseif ($Order->valueIsset('ref_order_id') && $Order->getValue('ref_order_id') > 0) {
                $output[] = '
                    <a href="' . rex_url::currentBackendPage(
                        [
                            'table_name' => $table,
                            'data_id'    => $Order->getValue('ref_order_id'),
                            'func'       => 'edit',
                            'ts'         => time(),
                        ]
                    ) . '" class="btn btn-primary">
                        <i class="fa fa-file-text-o"></i>&nbsp;
                        ' . rex_i18n::msg('action.goto_order') . '
                    </a>
                ';
            }

            if (\FriendsOfREDAXO\Simpleshop\Settings::getValue('use_shipping_tracking')) {
                $pjaxUrl = rex_url::currentBackendPage(
                    [
                        'table_name' => $table,
                        'data_id'    => $main_id,
                        'func'       => rex_request('func', 'string'),
                        'ss-action'  => 'save-tracking-link',
                        'ts'         => time(),
                    ]
                );

                $output[] = '
                    <hr style="border-color:#3ab694;margin:16px 0;"/>
                    <div data-tracking-url-container>
                        <strong>Tracking-Url</strong>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="tracking-link" value="'. $Order->getValue('shipping_tracking_url') .'" class="form-control"/>
                            </div> 
                            <div class="col-md-4">
                                <a href="' . $pjaxUrl . '" class="btn btn-primary" onclick="return SimpleshopBackend.saveTrackingUrl(this);">
                                    <i class="fa fa-save"></i>&nbsp;
                                    Link speichern
                                </a>
                            </div>
                        </div>
                    </div>
                ';
            }

            $this->params['form_output'][$this->getId()] = '
                <div class="row nested-panel">
                    <div class="form-group col-xs-12" id="' . $this->getHTMLId() . '">
                        <div>' . implode('', $output) . '</div>
                    </div>
                </div>
            ';
        }
    }

    public function getDefinitions($values = [])
    {
        return [
            'is_hiddeninlist' => true,
            'is_searchable'   => false,
            'dbtype'          => 'none',
            'type'            => 'value',
            'name'            => 'order_functions',
            'description'     => rex_i18n::msg("yform_values.order_functions_description"),
            'values'          => ['name' => ['type' => 'name', 'label' => rex_i18n::msg("yform_values_defaults_name")],],
        ];
    }
}