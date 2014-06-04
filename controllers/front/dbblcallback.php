<?php
/**
 * Created by PhpStorm.
 * User: fattah
 * Date: 5/15/14
 * Time: 12:55 PM
 */

include_once(_PS_MODULE_DIR_ . '/banglapay/api/dbbl_lib.php');

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
        $dbbl_lib = new DbblLib();
        $transaction_details = $dbbl_lib->verify_dbbl_transaction($dbbl_transaction_id);
        #Todo: Retrieve dbbl_payment fro db.
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'dbbl_payments where dbbl_transaction_id = \'' . $dbbl_transaction_id . '\' order by created_at desc';
        $dbbl_transaction = Db::getInstance()->getRow($sql);
        $success = false;
        if ($dbbl_lib->is_payment_complete($transaction_details)) {
            #TODO: check status was not successful before
            if (true) {
                #TODO::  update transaction status,
                Db::getInstance()->Execute('
	        UPDATE `' . _DB_PREFIX_ . 'dbbl_payments` set `status` = \'' . DbblLib::STATE_PAID . '\',
	        `result_code` = \'' . $transaction_details['response_hash']['RESULT_CODE'] . '\' ,
	        `result` = \'' . $transaction_details['response_hash']['RESULT'] . '\',
	        `dbbl_response` = \'' . $transaction_details['details'] . '\',
	        `updated_at` = \'' . date("Y-m-d H:i:s") . '\'
	        where `dbbl_transaction_id` = \'' . $dbbl_transaction_id . '\'');
                #TODO: update order status.
                $order_attributes = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders where id_cart = \'' .
                    $dbbl_transaction['cart_id'] . '\'');
                $objOrder = new Order($order_attributes['id_order']);
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $state = (int)Configuration::get('PS_OS_PAYMENT');
                $history->changeIdOrderState($state, (int)($objOrder->id));
                $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'order_history` (`id_order`, `id_order_state`, `date_add`, `id_employee`)
	            VALUES(' . (int)$order_attributes['id_order'] . ', ' . $state . ', \'' . date("Y-m-d H:i:s") . '\', 0)';
                Db::getInstance()->Execute($sql);

                $error_message = "Payment completed";
                $success = true;
            }
        } else {
            #TODO::  update transaction status,
            Db::getInstance()->Execute('
	        UPDATE `' . _DB_PREFIX_ . 'dbbl_payments` set `status` = \'' . DbblLib::STATE_FAILED . '\',
	        `result_code` = \'' . $transaction_details['response_hash']['RESULT_CODE'] . '\' ,
	        `result` = \'' . $transaction_details['RESULT'] . '\',
	        `dbbl_response` = \'' . $transaction_details['details'] . '\',
	        `updated_at` = \'' . date("Y-m-d H:i:s") . '\'
	        where `dbbl_transaction_id` = \'' . $dbbl_transaction_id . '\'');

            #TODO: update order status.
            $order_attributes = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders where id_cart = \'' .
                $dbbl_transaction['cart_id'] . '\'');
            $objOrder = new Order($order_attributes['id_order']);
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $state = (int)Configuration::get('PS_OS_ERROR');
            $history->changeIdOrderState($state, (int)($objOrder->id));
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'order_history` (`id_order`, `id_order_state`, `date_add`, `id_employee`)
	        VALUES(' . (int)$order_attributes['id_order'] . ', ' . $state . ', \'' . date("Y-m-d H:i:s") . '\', 0)';
            Db::getInstance()->Execute($sql);

            $error_message = "Payment failed";
            $success = false;
        }

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
            'dbbl_lib' => $dbbl_lib,
            'redirect_url' => $redirect_url,
            'cart' => $cart,
            'customer' => $customer,
            'time' => 'test',
            'callback_params' => array('trans_id' => $dbbl_transaction_id)
        ));
        //$this->setTemplate('dbbl_redirect.tpl');
        #TODO: show successful/failure message
        if($success == true)
            $this->setTemplate('dbbl_success.tpl');
        else
            $this->setTemplate('dbbl_failure.tpl');
    }
}
