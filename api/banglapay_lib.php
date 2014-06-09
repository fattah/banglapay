<?php
/**
 * Created by PhpStorm.
 * User: fattah
 * Date: 6/5/14
 * Time: 12:25 PM
 */

include_once('dbbl_lib.php');

class BanglapayLib{

    function update_payment_by_dbbl_transaction_id($dbbl_transaction_id){
        $dbbl_lib = new DbblLib();
        $transaction_details = $dbbl_lib->verify_dbbl_transaction($dbbl_transaction_id);
        #Todo: Retrieve dbbl_payment fro db.
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'dbbl_payments where dbbl_transaction_id = \'' . $dbbl_transaction_id . '\' order by created_at desc';
        $dbbl_payment = Db::getInstance()->getRow($sql);

        return $this->update_dbbl_payment($dbbl_payment, $transaction_details);
    }

    function update_payment_by_dbbl_payment_id($dbbl_payment_id){
        $dbbl_lib = new DbblLib();
        #Todo: Retrieve dbbl_payment fro db.
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'dbbl_payments where id = \'' . $dbbl_payment_id . '\' order by created_at desc';
        $dbbl_payment = Db::getInstance()->getRow($sql);
        $transaction_details = $dbbl_lib->verify_dbbl_transaction($dbbl_payment['dbbl_transaction_id']);

        return $this->update_dbbl_payment($dbbl_payment, $transaction_details);
    }

    function update_dbbl_payment($dbbl_payment, $transaction_details){
        $dbbl_lib = new DbblLib();

        $success = false;
        $error_message = "Payment failed";

        if ($dbbl_lib->is_payment_complete($transaction_details)) {
            #TODO: check status was not successful before
            if ($dbbl_payment["status"] != DbblLib::STATE_PAID) {
                #TODO::  update transaction status,
                Db::getInstance()->Execute('
	        UPDATE `' . _DB_PREFIX_ . 'dbbl_payments` set `status` = \'' . DbblLib::STATE_PAID . '\',
	        `result_code` = \'' . $transaction_details['response_hash']['RESULT_CODE'] . '\' ,
	        `result` = \'' . $transaction_details['response_hash']['RESULT'] . '\',
	        `dbbl_response` = \'' . $transaction_details['details'] . '\',
	        `updated_at` = \'' . date("Y-m-d H:i:s") . '\'
	        where `id` = \'' . $dbbl_payment["id"] . '\'');
                #TODO: update order status.
                $order_attributes = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders where id_cart = \'' .
                    $dbbl_payment['cart_id'] . '\'');
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
            else{
                $error_message = "Status cannot be changed of a previously completed payment.";
                $success = false;
            }
        } else {
            #TODO::  update transaction status,
            Db::getInstance()->Execute('
	        UPDATE `' . _DB_PREFIX_ . 'dbbl_payments` set `status` = \'' . DbblLib::STATE_FAILED . '\',
	        `result_code` = \'' . $transaction_details['response_hash']['RESULT_CODE'] . '\' ,
	        `result` = \'' . $transaction_details['RESULT'] . '\',
	        `dbbl_response` = \'' . $transaction_details['details'] . '\',
	        `updated_at` = \'' . date("Y-m-d H:i:s") . '\'
	        where `id` = \'' . $dbbl_payment["id"] . '\'');

            #TODO: update order status.
            $order_attributes = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'orders where id_cart = \'' .
                $dbbl_payment['cart_id'] . '\'');
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

        return array($success, $error_message);
    }
}
