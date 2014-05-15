<?php

class banglapay extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    function __construct()
    {
        $this->name = 'banglapay';
        $this->tab = 'payments_gateways';
        $this->author = 'Nascenia';
        $this->version = 1;
        $this->controllers = array('payment', 'dbblredirect');

        $this->bootstrap = true;
        parent::__construct(); // The parent construct is required for translations

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Dutch Bangla Payments Module');
        $this->description = $this->l('Dutch Bangla Payment');

    }

    public function install()
    {
        if (!parent::install() || !$this->createDbblPaymentTable() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('header'))
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->dropDbblPaymentTable())
            return false;
        return true;
    }

    public function hookPayment($params)
    {
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        //Logger::addLog("test log2", 1);

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_dbbl' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));
        return $this->display(__FILE__, 'hook/payment.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path.'banglapay.css', 'all');
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
            return;

        $state = $params['objOrder']->getCurrentState();

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }

    function createDbblPaymentTable()
    {
        $db = Db::getInstance();
        $query = "CREATE TABLE `"._DB_PREFIX_."dbbl_payments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) DEFAULT NULL,
          `status` varchar(255) DEFAULT NULL,
          `status_code` varchar(255) DEFAULT NULL,
          `card_number` varchar(255) DEFAULT NULL,
          `dbbl_transaction_id` varchar(255) DEFAULT NULL,
          `dbbl_request` varchar(255) DEFAULT NULL,
          `dbbl_response` varchar(500) DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB";

        $db->Execute($query);

        return true;
    }

    function dropDbblPaymentTable()
    {
        $db = Db::getInstance();
        $query = "DROP TABLE `"._DB_PREFIX_."dbbl_payments`";

        $db->Execute($query);

        return true;
    }
}