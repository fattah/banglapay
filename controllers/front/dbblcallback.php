<?php
/**
 * Created by PhpStorm.
 * User: fattah
 * Date: 5/15/14
 * Time: 12:55 PM
 */

include_once(_PS_MODULE_DIR_ . '/banglapay/api/banglapay_lib.php');

class BanglapayDbblcallbackModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart))
            Tools::redirect('index.php?controller=order');

        if (Tools::getValue('bangla_card_type') == "") {
            $error_message = "Please select a card type";
        }

        $dbbl_transaction_id = Tools::getValue('trans_id');
        $banglapay_lib = new BanglapayLib();
        list($success, $error_message) = $banglapay_lib->update_payment_by_dbbl_transaction_id(Tools::getValue('trans_id'), Tools::getRemoteAddr());

        $redirect_url = "";
        $total = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $customer = new Customer($this->context->cart->id_customer);
        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
            'bangla_card_type' => Tools::getValue('bangla_card_type'),
            'error_message' => $error_message,
            'redirect_url' => $redirect_url,
            'cart' => $cart,
            'customer' => $customer,
            'time' => 'test',
            'callback_params' => array('trans_id' => $dbbl_transaction_id)
        ));

        #TODO: show successful/failure message
        if($success == true)
            $this->setTemplate('dbbl_success.tpl');
        else
            $this->setTemplate('dbbl_failure.tpl');
    }
}
