<?php
/**
* 
*/
class LookbookLooksProducts extends ObjectModel
{
  public $id_lookbook_looks_product	;
  public $id_look;
  public $id_product;

  protected $fieldsRequired = array('id_look', 'id_product');
  protected $fieldsValidate = array('id_look' => 'isUnsignedInt', 'id_product' => 'isUnsignedInt');

  protected $table = 'lookbook_looks_products';
  protected $identifier = 'id_lookbook_looks_product';

  public function getFields() 
  { 
    parent::validateFields();
    $fields['id_lookbook_looks_product'] = (int)($this->id);
    $fields['id_look'] = (int)($this->id_look);
    $fields['id_product'] = (int)($this->id_product);
    return $fields;  
  }
  
  public static function deleteLookProducts($id_look)
  {
    $results = Db::getInstance()->Execute("SELECT `id_lookbook_looks_product` FROM `" . _DB_PREFIX_ . "lookbook_looks_products` WHERE `id_look` = " . $id_look);
    
    foreach($results AS $id)
    {
      $obj = new LookbookLooks($id);

      if(!$obj->delete())
        return false;
    }
    return true;
  }
}
?>