<?php
/**
* 
*/
class LookbookLooks extends ObjectModel
{
  public $id_look;
  public $id_lookbook;
  public $id_cms;

  protected $fieldsRequired = array('id_lookbook', 'id_cms');
  protected $fieldsValidate = array('id_lookbook' => 'isUnsignedInt', 'id_cms' => 'isUnsignedInt');

  protected $table = 'lookbook_looks';
  protected $identifier = 'id_look';

  public function getFields() 
  { 
    parent::validateFields();
    $fields['id_look'] = (int)($this->id);
    $fields['id_lookbook'] = (int)($this->id_lookbook);
    $fields['id_cms'] = (int)($this->id_cms);
    return $fields;  
  }
}
?>