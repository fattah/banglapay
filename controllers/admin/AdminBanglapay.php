<?php

/**
 * Created by PhpStorm.
 * User: fattah
 * Date: 6/3/14
 * Time: 6:43 PM
 */

include_once(_PS_MODULE_DIR_ . '/banglapay/api/banglapay_lib.php');

class AdminBanglapayController extends ModuleAdminController
{

    public function __construct()
    {
        //$tab = new Tab((int)Tab::getIdFromClassName('AdminBanglaTransactionXML')); $tab->delete();
        //$tab = new Tab((int)Tab::getIdFromClassName('AdminBanglaPay')); $tab->delete();
        if(Tools::getValue('format') == "xml")
        {
            $this->renderXML();
            parent::__construct();
            return;
        }

        $this->className = 'DbblPaymentModel';
        $this->table = 'dbbl_payments';
        $this->identifier = "id";
        $this->_defaultOrderBy = 'created_at';
        $this->_defaultOrderWay = 'desc';
        $this->orderBy = "created_at";
        $this->orderWay = "desc";
        $this->meta_title = $this->l('DBBL payment attempts');
        $this->deleted = false;
        //$this->explicitSelect = true;
        $this->context = Context::getContext();
        //$this->lang = true;
        $this->bootstrap = true;

        $this->sortedTree = array();

        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }

        $this->addRowAction('edit');
//        $this->addRowAction('delete');
        $this->addRowAction('details');
        $this->addRowAction('updatestatus');

        $this->fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'type' => 'int',
                'align' => 'center',
                'width' => 25,
            ),
            'cart_id' => array(
                'title' => $this->l('Cart id'),
                'width' => 'auto',
                'orderby' => false
            ),
            'order_id' => array(
                'title' => $this->l('Order id'),
                'width' => 'auto',
                'orderby' => false
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'width' => 'auto',
                'orderby' => false
            ),
            'result' => array(
                'title' => $this->l('Result'),
                'width' => 70,
                'align' => 'center',
                'orderby' => false
            ),
            'result_code' => array(
                'title' => $this->l('Result code'),
                'width' => 70,
                'align' => 'center',
                'orderby' => false
            ),
            'dbbl_transaction_id' => array(
                'title' => $this->l('Transaction id'),
                'width' => 70,
                'align' => 'center',
                'orderby' => false
            ),
            'dbbl_response' => array(
                'title' => $this->l('Response'),
                'width' => 70,
                'align' => 'center',
                'orderby' => false
            ),
            'created_at' => array(
                'title' => $this->l('Created at'),
                'width' => 70,
                'align' => 'center',
                'orderby' => true
            ),
            'updated_at' => array(
                'title' => $this->l('Updated at'),
                'width' => 70,
                'align' => 'center',
                'orderby' => true
            )
        );

        parent::__construct();
    }

    /* ------------------------------------------------------------- */
    /*  INIT PAGE HEADER TOOLBAR
    /* ------------------------------------------------------------- */
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn = array(
                'new' => array(
                    'href' => self::$currentIndex . '&addwpblog_cats&token=' . $this->token,
                    'desc' => $this->l('Add New DBBL Payment', null, null, false),
                    'icon' => 'process-icon-new'
                )
            );
        }

        parent::initPageHeaderToolbar();
    }

    /* ------------------------------------------------------------- */
    /*  INCLUDE NECESSARY FILES
    /* ------------------------------------------------------------- */
    public function setMedia()
    {
        parent::setMedia();
        //$this->addCSS(__PS_BASE_URI__.'modules/wpblog/views/css/admin/wpblog.css');
    }

    /* ------------------------------------------------------------- */
    /*  OVERRIDE THE METHOD SO WE CAN REBUILD THE LIST
    /* ------------------------------------------------------------- */
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        //$this->_list = $this->_rebuildList($this->_list);
    }



//    /* ------------------------------------------------------------- */
//    /*  DELETE THE CATEGORY
//    /* ------------------------------------------------------------- */
//    public function processDelete()
//    {
//        // Check if category is deletable
//        if (Validate::isInt(Tools::getValue('id_wpblog_cats'))) {
//            $wpBlogCategory = new WPBlogCategoryModel(Tools::getValue('id_wpblog_cats'));
//            if (Validate::isLoadedObject($wpBlogCategory) && $wpBlogCategory->deletable) {
//                return parent::processDelete();
//            }
//        }
//
//        return false;
//    }

    public function processDetails()
    {
        $payment_id = Tools::getValue('id');
        $this->context->controller->errors = array("This is a processDetails error");

        return false;
    }

    public function renderForm()
    {
        echo "test";
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Blog Category'),
                'icon' => 'icon-edit'
            ),
            // Inputs
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Parent Category'),
                    'name' => 'result'
                ),
            ));

        return parent::renderForm();
    }

    public function processUpdate()
    {
//        $payment_id = Tools::getValue('id');
//        $this->context->controller->errors = array("This is a processEdit error");
        echo "test";

        return true; //parent::processUpdate();
    }

    public function processUpdatestatus()
    {
        $dbbl_transaction_id = Tools::getValue('trans_id');
        $banglapay_lib = new BanglapayLib();
        list($success, $message) = $banglapay_lib->update_payment_by_dbbl_payment_id(Tools::getValue('id'));

        if ($success == true) {
            $this->context->controller->informations = array($message);
        } else {
            $this->context->controller->errors = array($message);
        }

        return true;
    }

    public function displayUpdatestatusLink($token, $id)
    {
        $tpl = $this->createTemplate('list_action_update_status.tpl');
        $tpl->assign(array(
            'href' => self::$currentIndex . '&token=' . $this->token . '&' .
                $this->identifier . '=' . $id . '&updatestatus' . $this->table . '=1',
            'action' => $this->l('UpdateStatus')
        ));

        return $tpl->fetch();
    }

    public function initProcess()
    {
        parent::initProcess();
        if (Tools::getValue('updatestatus' . $this->table)) {
            $this->display = 'updatestatus';
            $this->action = 'updatestatus';
        }
    }

    //URL index.php?controller=AdminBanglapay&token=xyz&startdate=2014-06-03&enddate=2014-06-04&format=xml
    public function renderXML()
    {
        $this->setTemplate('xml.tpl');
        #TODO:: move merchant information from xml file to config file.
    }

    public function initContent()
    {
        parent::initContent();

        if(Tools::getValue('format') == "xml")
        {
            $startdate = Tools::getValue('startdate');
            $enddate = Tools::getValue('enddate');

            $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'dbbl_payments where created_at >= \'' . $startdate . ' 00:00:00\' and created_at <=\'' . $enddate . ' 23:59:59\' order by created_at desc';
            $results = Db::getInstance()->ExecuteS($sql);
            foreach ($results as $row){
                //echo $row['dbbl_transaction_id'];
            }

            $this->context->smarty->assign(array(
                'results' => $results
            ));
        }
    }
}