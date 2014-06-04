<?php

class DbblPaymentModel extends ObjectModel
{
    private $_catTree = array();

    public $id;
    public $cart_id;
    public $order_id;
    public $status;
    public $result;
    public $result_code;
    public $card_number;
    public $dbbl_transaction_id;
    public $dbbl_request;
    public $dbbl_response;
    public $created_at;
    public $updated_at;

    //Multilang Fields
    public $name;
    public $description;
    public $meta_description;
    public $link_rewrite;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'dbbl_payments',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            //Fields
            'cart_id' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'order_id'              =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'status'          =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'result'             =>  array('type' => self::TYPE_INT),
            'result_code'       =>  array('type' => self::TYPE_STRING),
            'card_number'           =>  array('type' => self::TYPE_STRING),
            'dbbl_transaction_id'           =>  array('type' => self::TYPE_STRING),
            'dbbl_request'           =>  array('type' => self::TYPE_STRING),
            'dbbl_response'           =>  array('type' => self::TYPE_STRING),
            'created_at'           =>  array('type' => self::TYPE_STRING),
            'updated_at'           =>  array('type' => self::TYPE_STRING),

            //Multilanguage Fields
            'name'              =>  array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 250),
            'description'       =>  array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 250),
            'meta_description'  =>  array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 250),
            'link_rewrite'      =>  array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 250),
        )
    );

    public function __construct($id_wpblog_cats = null, $id_lang = null, $id_shop = null)
    {
        Shop::addTableAssociation('dbbl_payments', array('type' => 'shop'));
        parent::__construct($id_wpblog_cats, $id_lang, $id_shop);
    }

    /*-------------------------------------------------------------*/
    /*  DELETE
    /*-------------------------------------------------------------*/
    public function delete()
    {
        $deletedId = $this->id;

        $response = parent::delete();

        return true;
    }

    /*-------------------------------------------------------------*/
    /*  TEST FOR USER ACCESS
    /*-------------------------------------------------------------*/
    public function isAccessGranted()
    {
        if ($userGroups = Context::getContext()->customer->getGroups()){

            // If the object not set
            if (!isset($this->id)){
                return false;
            }

            // Check user groups

            // Post groups are stored in db in different way, so we need to fix that
            $tmpPostGroups = unserialize($this->group_access);
            $postGroups = array();

            foreach ($tmpPostGroups as $groupID => $status){
                if ($status){
                    $postGroups[] = $groupID;
                }
            }

            // Check if groups are intersecting
            $intersect = array_intersect($userGroups, $postGroups);
            if (count($intersect)){
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /*-------------------------------------------------------------*/
    /*  ADD CATEGORIES TO SHOPS
    /*-------------------------------------------------------------*/
    public function addBlogCategoryToShops($id_wpblog_cats)
    {
        $shops = Shop::getShops(true, null, true);

        foreach ($shops as $key => $id_shop){
            $row = array('id_wpblog_cats' => $id_wpblog_cats, 'id_shop' => $id_shop);
            Db::getInstance()->insert('wpblog_cats_shop', $row, false, true, Db::INSERT_IGNORE);
        }
    }

}
