<?php
/*
* 2007-2014 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */

include_once(_PS_MODULE_DIR_ . '/banglapay/api/dbbl_lib.php');

class BanglapayDbblredirectModuleFrontController extends ModuleFrontController
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

        if(Tools::getValue('bangla_card_type') == ""){
            $error_message = "Please select a card type";
        }

        $dbbl_lib = new DbblLib();
        $command_output = $dbbl_lib->system_call("ls -la");
        $transaction_information = $dbbl_lib->create_transaction($cart->getOrderTotal(true, Cart::BOTH), "Goponjinish payment for cart id " . $cart->id_cart, $cart->id_cart, Tools::getValue('bangla_card_type'));
        $redirect_url = $transaction_information["payment_url"];

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
            'redirect_url' => $redirect_url
        ));

        if(Tools::getValue('bangla_card_type') == ""){
            $error_message = "Please select a card type";
            $this->setTemplate('select_card_type.tpl');
        }
        else{
            $this->setTemplate('dbbl_redirect.tpl');
            //Tools::redirectLink('http://www.dutchbanglabank.com');
            //Tools::redirectLink($redirect_url);
        }
    }
}
