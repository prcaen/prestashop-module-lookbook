<?php
/**
* 
*/
class LookbookC extends ObjectModel
{
  public $id_lookbook;
  public $id_cms;
  public $id_cms_category;

  protected $fieldsRequired = array('id_cms', 'id_cms_category');
  protected $fieldsValidate = array('id_cms' => 'isUnsignedInt', 'id_cms_category' => 'isUnsignedInt');

  protected $table = 'lookbook';
  protected $identifier = 'id_lookbook';

  public function getFields() 
  { 
    parent::validateFields();
    $fields['id_lookbook'] = (int)($this->id);
    $fields['id_cms'] = (int)($this->id_cms);
    $fields['id_cms_category'] = (int)($this->id_cms_category);
    return $fields;  
  }
  
  public static function getNextId()
  {
    $results = Db::getInstance()->ExecuteS('SHOW TABLE STATUS FROM ' . _DB_NAME_);
    foreach($results as $result)
    {
      if($result['Name'] == _DB_PREFIX_ . 'cms')
        return $result['Auto_increment'];
    }
  }
}
?>