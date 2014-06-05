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
        $this->className = 'DbblPaymentModel';
        $this->table = 'dbbl_payments';
        $this->identifier = "id";
        $this->orderBy = "created_at";
        $this->orderWay = "desc";
        $this->meta_title = $this->l('DBBL payment attempts');
        $this->deleted = false;
        $this->explicitSelect = true;
        $this->context = Context::getContext();
        //$this->lang = true;
        $this->bootstrap = true;

        $this->sortedTree = array();

        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }

        $this->addRowAction('edit');
        $this->addRowAction('delete');
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
//        $this->addCSS(__PS_BASE_URI__.'modules/wpblog/views/css/admin/wpblog.css');
    }

    /* ------------------------------------------------------------- */
    /*  DISPLAY BLOG CATEGORIES LIST
    /* ------------------------------------------------------------- */
    public function _getBlogCategoriesList()
    {
        $id_default_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $wpBlogCategory = new DbblPaymentModel();

        return $wpBlogCategory;
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
//    /*  REBUILD THE LIST
//    /* ------------------------------------------------------------- */
//    public function _rebuildList(&$list, $parent = 0)
//    {
//        foreach ($list as $key => $listItem) {
//            $wpblogCategory = new WPBlogCategoryModel($listItem['id_wpblog_cats']);
//            $id_parent_category = $wpblogCategory->id_parent_category;
//            $depth = $wpblogCategory->depth;
//
//            if ($id_parent_category == $parent) {
//                $listItem['name'] = str_repeat('—', $depth) . ' ' . $listItem['name'];
//                $this->sortedTree[] = $listItem;
//
//                // Remove the delete icon from "Uncategorized" category
//                if (!$wpblogCategory->deletable) {
//                    $this->addRowActionSkipList('delete', array($listItem['id_wpblog_cats']));
//                }
//
//                unset($list[$key]);
//                $this->_rebuildList($list, $listItem['id_wpblog_cats']);
//            }
//        }
//
//        reset($list);
//        return $this->sortedTree;
//    }

    /* ------------------------------------------------------------- */
    /*  RENDER ADD/EDIT FORM
    /* ------------------------------------------------------------- */
    public function renderForm()
    {
        $id_default_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $languages = $this->context->language->getLanguages();

        /* Render Form */

        // Get Blog Categories
        $blogCategories[] = array(
            'id' => "0",
            'dbbl_transaction_id' => '-'
        );

        if (Tools::getValue('id') != '') {
            $wpBlogCategory = new WPBlogCategoryModel;
            $categoryTree = $wpBlogCategory->renderBlogCategoryTree($id_shop, $id_default_lang, array(Tools::getValue('id'), 1, 2), false, true);
            $blogCategories = array_merge($blogCategories, $categoryTree);
        } else {
            $wpBlogCategory = new WPBlogCategoryModel;
            $categoryTree = $wpBlogCategory->renderBlogCategoryTree($id_shop, $id_default_lang, array(1, 2), false, true);
            $blogCategories = array_merge($blogCategories, $categoryTree);
        }

        // If the object is available, we are editing the item
        // Group access
        if ($this->object && $this->object->group_access) {
            $groupAccess = unserialize($this->object->group_access);

            foreach ($groupAccess as $groupAccessID => $value) {
                $groupBox = 'groupBox_' . $groupAccessID;
                $this->fields_value[$groupBox] = $value;
            }
        } else {
            $groups = Group::getGroups($id_default_lang);
            $preselected = array(
                Configuration::get('PS_UNIDENTIFIED_GROUP'),
                Configuration::get('PS_GUEST_GROUP'),
                Configuration::get('PS_CUSTOMER_GROUP')
            );
            foreach ($groups as $group) {
                $this->fields_value['groupBox_' . $group['id_group']] = (in_array($group['id_group'], $preselected));
            }
        }

        // Init Fields form array
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('DBBL transactions'),
                'icon' => 'icon-edit'
            ),
            // Inputs
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Parent Category'),
                    'name' => 'id_parent_category',
                    'options' => array(
                        'query' => $blogCategories,
                        'id' => 'id_wpblog_cats',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Category Name'),
                    'desc' => $this->l('Must be less than 250 characters.'),
                    'name' => 'name',
                    'class' => 'copy2friendlyUrl',
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Category Description'),
                    'desc' => $this->l('Category description will show up below the title in category pages. Must be less than 250 characters.'),
                    'name' => 'description',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta Description'),
                    'desc' => $this->l('Meta description of your category page. Must be less than 250 characters.'),
                    'name' => 'meta_description',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friendly URL'),
                    'desc' => $this->l('Only letters and dash(-) allowed.'),
                    'name' => 'link_rewrite',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Displayed:'),
                    'name' => 'active',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),
                array(
                    'type' => 'group',
                    'label' => $this->l('Group access:'),
                    'name' => 'groupBox',
                    'values' => Group::getGroups($id_default_lang),
                    'unidentified' => 'Unidetified',
                    'guest' => 'Guest',
                    'customer' => 'Customer',
                    'info_introduction' => $this->l('You now have three default customer groups.'),
                    'desc' => $this->l('Mark all of the customer groups you`d like to have access to this category.')
                )
            ),
            // Submit Button
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'saveBlogCategory'
            )
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        if ($this->object && $this->object->id_wpblog_cats) {
            $this->tpl_form_vars['id_wpblog_cats'] = $this->object->id_wpblog_cats;
        }

        return parent::renderForm();
    }

    /* ------------------------------------------------------------- */
    /*  SAVE THE CATEGORY
    /* ------------------------------------------------------------- */
    public function processAdd()
    {
        $id_shop = $this->context->shop->id;
        $id_default_lang = $this->context->language->id;

        // Set the depth
        if (Validate::isInt(Tools::getValue('id_parent_category')) && Tools::getValue('id_parent_category') != 0) {
            $depth = WPBlogCategoryModel::getBlogCategoryDepthById(Tools::getValue('id_parent_category'));
            $_POST['depth'] = $depth + 1;
        } else {
            $_POST['depth'] = 0;
        }

        // Set the deletable status
        $_POST['deletable'] = 1;

        // Set the group access
        $groups = Group::getGroups($id_default_lang);
        $groupBox = array();
        $groupBox = Tools::getValue('groupBox');

        if (!$groupBox) {
            foreach ($groups as $group) {
                $access[$group['id_group']] = false;
            }
        } else {
            foreach ($groups as $group) {
                $access[$group['id_group']] = in_array($group['id_group'], $groupBox);
            }
        }

        $access = serialize($access);
        $_POST['group_access'] = $access;

        return parent::processAdd();
    }


    /* ------------------------------------------------------------- */
    /*  UPDATE THE CATEGORY
    /* ------------------------------------------------------------- */
    public function processUpdate()
    {
        $id_default_lang = $this->context->language->id;

        // Set the depth
        if (Validate::isInt(Tools::getValue('id_parent_category')) && Tools::getValue('id_parent_category') != 0) {
            $depth = WPBlogCategoryModel::getBlogCategoryDepthById(Tools::getValue('id_parent_category'));
            $_POST['depth'] = $depth + 1;
        } else {
            $_POST['depth'] = 0;
        }

        // Set the group access
        $groups = Group::getGroups($id_default_lang);
        $groupBox = array();
        $groupBox = Tools::getValue('groupBox');

        if (!$groupBox) {
            foreach ($groups as $group) {
                $access[$group['id_group']] = false;
            }
        } else {
            foreach ($groups as $group) {
                $access[$group['id_group']] = in_array($group['id_group'], $groupBox);
            }
        }

        $access = serialize($access);
        $_POST['group_access'] = $access;

        return parent::processUpdate();
    }


    /* ------------------------------------------------------------- */
    /*  DELETE THE CATEGORY
    /* ------------------------------------------------------------- */
    public function processDelete()
    {
        // Check if category is deletable
        if (Validate::isInt(Tools::getValue('id_wpblog_cats'))) {
            $wpBlogCategory = new WPBlogCategoryModel(Tools::getValue('id_wpblog_cats'));
            if (Validate::isLoadedObject($wpBlogCategory) && $wpBlogCategory->deletable) {
                return parent::processDelete();
            }
        }

        return false;
    }

    public function processUpdatestatus()
    {
        $dbbl_transaction_id = Tools::getValue('trans_id');
        $banglapay_lib = new BanglapayLib();
        list($success, $error_message) = $banglapay_lib->update_payment_by_dbbl_payment_id(Tools::getValue('id'));

        if($success == true){
            $this->context->controller->informations = array("Payment successful");
        }else{
            $this->context->controller->errors = array("Payment failed");
        }

        return true;
    }

    public function displayUpdatestatusLink($token, $id)
    {
        $tpl = $this->createTemplate('list_action_update_status.tpl');
        $tpl->assign(array(
            'href' => self::$currentIndex.'&token='.$this->token.'&'.
                $this->identifier.'='.$id.'&updatestatus'.$this->table.'=1',
            'action' => $this->l('UpdateStatus')
        ));

        return $tpl->fetch();
    }

    public function initProcess()
    {
        parent::initProcess();
        if (Tools::getValue('updatestatus'.$this->table))
        {
            $this->display = 'updatestatus';
            $this->action = 'updatestatus';
        }
    }

}