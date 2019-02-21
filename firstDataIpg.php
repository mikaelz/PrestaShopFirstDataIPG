<?php
/**
 *  First Data Internet Payment Gateway.
 *
 *  @category PaymentModule
 *
 *  @author   Michal Zuber <info@nevilleweb.sk>
 *  @license  MIT License
 *
 *  @link     https://www.nevilleweb.sk/
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class firstDataIpg extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'firstDataIpg';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Michal Zuber';
        $this->need_instance = 1;
        $this->test_mode = false;
        $this->storeId = REPLACE_WITH_STORE_ID;
        $this->sharedSecret = REPLACE_WITH_SHARED_SECRET;

        parent::__construct();

        $this->displayName = $this->l('First Data IPG');
        $this->description = $this->l('First Data Internet Payment Gateway ');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->limited_countries = array('SK', 'CZ');

        $this->limited_currencies = array('EUR');
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        Configuration::updateValue('FIRSTDATAIPG_LIVE_MODE', true);

        include dirname(__FILE__).'/sql/install.php';

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayPayment');
    }

    public function uninstall()
    {
        Configuration::deleteByName('FIRSTDATAIPG_LIVE_MODE');

        include dirname(__FILE__).'/sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitFirstDataIpgModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitFirstDataIpgModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'FIRSTDATAIPG_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'FIRSTDATAIPG_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'FIRSTDATAIPG_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'FIRSTDATAIPG_LIVE_MODE' => Configuration::get('FIRSTDATAIPG_LIVE_MODE', true),
            'FIRSTDATAIPG_ACCOUNT_EMAIL' => Configuration::get('FIRSTDATAIPG_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'FIRSTDATAIPG_ACCOUNT_PASSWORD' => Configuration::get('FIRSTDATAIPG_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // $this->context->controller->addJS($this->_path.'/views/js/front.js');
        // $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookDisplayPayment($params)
    {
        if (!$this->active) {
            return;
        }

        $gateway_url = $this->test_mode ? 'https://test.ipg-online.com/connect/gateway/processing' : 'https://www.ipg-online.com/connect/gateway/processing';
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        date_default_timezone_set('Europe/Prague');
        $txndatetime = date('Y:m:d-H:i:s');

        $context = $this->context;
        $delivery_address = new Address(intval($params['cart']->id_address_delivery));
        $country = new Country(intval($delivery_address->id_country));
        $customer = new Customer(intval($delivery_address->id_customer));
        $args = array(
            'txntype' => 'sale',
            'timezone' => 'Europe/Prague',
            'txndatetime' => $txndatetime,
            'storename' => $this->storeId,
            'chargetotal' => number_format($context->cart->getOrderTotal(true), 2, '.', ''),
            'currency' => 978,
            'bcompany' => $delivery_address->company,
            'bname' => $delivery_address->firstname.' '.$delivery_address->lastname,
            'baddr1' => substr($delivery_address->address1, 0, 30),
            'baddr2' => substr($delivery_address->address2, 0, 30),
            'bcity' => substr($delivery_address->city, 0, 30),
            'bstate' => '',
            'bcountry' => $country->iso_code,
            'bzip' => $delivery_address->postcode,
            'phone' => substr($delivery_address->phone_mobile, 0, 20),
            'fax' => '',
            'email' => substr($customer->email, 0, 254),
            'oid' => $params['cart']->id,
            'invoicenumber' => $params['cart']->id,
            'transactionNotificationURL' => '',
            'responseSuccessURL' => $context->link->getModuleLink($this->name, 'validation', [], true),
            'responseFailURL' => $context->link->getModuleLink($this->name, 'validation', [], true),
            'hash_algorithm' => 'SHA256',
            'checkoutoption' => 'combinedpage',
            'language' => 'en_US',
        );

        $hash_base = $args['storename'].$args['txndatetime'].$args['chargetotal'].$args['currency'].$this->sharedSecret;
        $hash_hex = bin2hex($hash_base);
        $args['hash'] = hash('sha256', $hash_hex);

        Db::getInstance()->insert('first_data_ipg', array(
            'id_cart' => (int) $params['cart']->id,
            'txndatetime' => $txndatetime,
            'request' => pSQL(var_export($args, true)),
            'user_agent' => pSQL($_SERVER['HTTP_USER_AGENT']),
            'remote_addr' => pSQL($_SERVER['REMOTE_ADDR']),
            'created' => date('Y-m-d H:i:s'),
            'updated' => date('Y-m-d H:i:s'),
        ));

        $this->smarty->assign(array(
            'args' => $args,
            'gateway_url' => $gateway_url,
        ));

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }
}
