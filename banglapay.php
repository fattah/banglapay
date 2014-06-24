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
        $this->displayName = $this->l('Dutch Bangla Payment');
        $this->description = $this->l('Accepts payment by DBBL cards (Nexus, Master Visa)');
    }

    public function install()
    {
        if (!parent::install() || !$this->createDbblPaymentTable() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn') || !$this->registerHook('header') || !$this->installTab())
            return false;
        $this->setupStatus();
        $this->setupDefaultConfigurationValues();
        $command = "rm -f " . _PS_THEME_DIR_ . "modules/" . $this->name . " && ln -s " . _PS_MODULE_DIR_ . $this->name . "/themes/autumn/modules/" . $this->name . " " . _PS_THEME_DIR_ . "modules/" . $this->name . " 2>&1";
        echo "Command: " . $command;
        file_put_contents(_PS_ROOT_DIR_ . "/log/dbbl-commands.log", $command . "\n", FILE_APPEND);
        $output = shell_exec($command);
        file_put_contents(_PS_ROOT_DIR_ . "/log/dbbl-commands.log", $output . "\n", FILE_APPEND);
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallTab())
            return false;
//        if(!$this->dropDbblPaymentTable())
//            return false;
        return true;
    }

    public function installTab()
    {
        $parentTab = new Tab();
        $parentTab->active = 1;
        $parentTab->name = array();
        $parentTab->class_name = "AdminBanglapay";
        foreach (Language::getLanguages() as $lang) {
            $parentTab->name[$lang['id_lang']] = "Banglapay";
        }
        $parentTab->id_parent = 0;
        $parentTab->module = $this->name;
        $response = $parentTab->add();

        return $response;
    }

    public function uninstallTab()
    {
        $id_tabs = array(
            (int)Tab::getIdFromClassName('AdminBanglapay')
        );

        foreach ($id_tabs as $id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        return true;
    }

    public function setupStatus()
    {
        // check if the order status is defined
        if (!defined('_PS_OS_AWAITING_DBBL_PAYMENT_')) {
            // order status is not defined - check if, it exists in the table
            $rq = Db::getInstance()->getRow('
	    SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_state_lang`
	    WHERE id_lang = \'' . pSQL('1') . '\' AND  name = \'' . pSQL('Awaiting DBBL payment') . '\'');
            if ($rq && isset($rq['id_order_state']) && intval($rq['id_order_state']) > 0) {
                // order status exists in the table - define it.
                define('_PS_OS_AWAITING_DBBL_PAYMENT_', $rq['id_order_state']);
            } else {
                // order status doesn't exist in the table
                // insert it into the table and then define it.
                Db::getInstance()->Execute('
	        INSERT INTO `' . _DB_PREFIX_ . 'order_state` (`unremovable`, `color`, `module_name`) VALUES(1, \'lightblue\', \'' . $this->name . '\')');
                $stateid = Db::getInstance()->Insert_ID();
                Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'order_state_lang` (`id_order_state`, `id_lang`, `name`)
	        VALUES(' . intval($stateid) . ', 1, \'Awaiting DBBL payment\')');
                define('_PS_OS_AWAITING_DBBL_PAYMENT_', $stateid);
                Configuration::updateValue('PS_OS_AWAITING_DBBL_PAYMENT', $stateid);
            }
        }
    }

    public function setupDefaultConfigurationValues()
    {
        if (Configuration::get('BANGLAPAY_DBBL_LIB_DIRECTORY') == null || Configuration::get('BANGLAPAY_DBBL_LIB_DIRECTORY') == "")
            Configuration::updateValue('BANGLAPAY_DBBL_LIB_DIRECTORY', "/home/deployer/dbbl/production");
        if (Configuration::get('BANGLAPAY_TRANSACTION_ID_PREFIX') == null || Configuration::get('BANGLAPAY_TRANSACTION_ID_PREFIX') == "")
            Configuration::updateValue('BANGLAPAY_TRANSACTION_ID_PREFIX', "gj-");

        return true;
    }

    public function hookPayment($params)
    {
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_dbbl' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path . 'banglapay.css', 'all');
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
        $query = "CREATE TABLE `" . _DB_PREFIX_ . "dbbl_payments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `cart_id` int(11) DEFAULT NULL,
          `order_id` int(11) DEFAULT NULL,
          `status` varchar(255) DEFAULT NULL,
          `result` varchar(255) DEFAULT NULL,
          `result_code` varchar(255) DEFAULT NULL,
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
        $query = "DROP TABLE `" . _DB_PREFIX_ . "dbbl_payments`";

        $db->Execute($query);

        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('banglapay_config')) {
            $this->updateConfiguration();
        }
        $this->loadConfiguration();

        return $this->display(__FILE__, 'config.tpl');
    }

    public function loadConfiguration()
    {
        $dbbl_lib_directory = Configuration::get('BANGLAPAY_DBBL_LIB_DIRECTORY');
        $transaction_id_prefix = Configuration::get('BANGLAPAY_TRANSACTION_ID_PREFIX');
        $this->context->smarty->assign('banglapay_lib_dir', $dbbl_lib_directory);
        $this->context->smarty->assign('banglapay_transaction_id_prefix', $transaction_id_prefix);
    }

    public function updateConfiguration()
    {
        Configuration::updateValue('BANGLAPAY_DBBL_LIB_DIRECTORY', Tools::getValue('banglapay_lib_dir'));
        Configuration::updateValue('BANGLAPAY_TRANSACTION_ID_PREFIX', Tools::getValue('banglapay_transaction_id_prefix'));
    }

}