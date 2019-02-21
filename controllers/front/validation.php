<?php
/**
* 2007-2017 PrestaShop.
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class FirstDataIpgValidationModuleFrontController extends ModuleFrontController
{
    /**
     * This class should be use by your Instant Payment
     * Notification system to validate the order remotely.
     */
    public function postProcess()
    {
        /*
         * If the module is not active anymore, no need to process anything.
         */
        if ($this->module->active == false) {
            die;
        }

        $cart_id = (int) $_POST['oid'];
        $customer_id = $this->context->customer->id;
        $amount = number_format(str_replace(array(',', ' '), array('.', ''), $_POST['chargetotal']), 2, '.', '');

        Db::getInstance()->update(
            'first_data_ipg',
            array(
                'response' => pSQL(var_export($_POST, true)),
                'updated' => date('Y-m-d H:i:s'),
            ),
            "id_cart = $cart_id"
        );

        /*
         * Restore the context from the $cart_id & the $customer_id to process the validation properly.
         */
        Context::getContext()->cart = new Cart((int) $cart_id);
        Context::getContext()->customer = new Customer((int) $customer_id);
        Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int) Context::getContext()->customer->id_lang);

        $secure_key = Context::getContext()->customer->secure_key;

        if ($this->isValidOrder() === true) {
            $payment_status = Configuration::get('PS_OS_PAYMENT');
            $message = null;
        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');

            $message = $this->module->l('An error occurred while processing payment');
        }

        $module_name = $this->module->displayName;
        $currency_id = (int) Context::getContext()->currency->id;

        $this->module->validateOrder($cart_id, $payment_status, $amount, $module_name, $message, array(), $currency_id, false, $secure_key);

        $order_id = Order::getOrderByCartId((int)$cart_id);

        if ($order_id && ($secure_key == $this->context->customer->secure_key)) {
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$secure_key);
        } else {
            $this->errors[] = $this->module->l('An error occured. Please contact the merchant to have more informations');
            return $this->setTemplate('error.tpl');
        }
    }

    protected function isValidOrder()
    {
        $sql = 'SELECT txndatetime FROM '._DB_PREFIX_.'first_data_ipg WHERE id_cart = '.(int) $_POST['oid'].' ORDER BY id DESC';
        $txndatetime = Db::getInstance()->getValue($sql);

        $approval_code = substr($_POST['approval_code'], 0, 1);
        $hash_base = implode('', array(
            $this->module->sharedSecret,
            $_POST['approval_code'],
            $_POST['chargetotal'],
            $_POST['currency'],
            $txndatetime,
            $this->module->storeId,
        ));
        $hash = hash('sha256', bin2hex($hash_base));

        $isValid = false;
        switch ($approval_code) {
            case 'Y' :
                if ($hash == $_POST['response_hash']) {
                    $isValid = true;
                }
            break;
        }

        return $isValid;
    }
}
