<?php
/**
* 
*/
include_once(_PS_MODULE_DIR_ . 'lookbook/classes/Lookbook.php');
include_once(_PS_MODULE_DIR_ . 'lookbook/classes/LookbookLooksProducts.php');
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

  public function getProducts($id_lang)
  {
    $sql = "SELECT l.`id_product`
            FROM `" . _DB_PREFIX_ . "lookbook_looks_products` l
            WHERE l.`id_look` = " . (int)$this->id_look;
    $results = Db::getInstance()->ExecuteS($sql);

    $datas = array();
    foreach($results as $result)
    {
      $datas['products'][] = new Product($result['id_product'], false, $id_lang);
      $datas['images'][]   = Product::getCover($result['id_product']);
    }
    return $datas;
  }

  public static function getObjectFromCmsId($id_cms)
  {
    $id = Db::getInstance()->getValue("SELECT `id_look` FROM `" . _DB_PREFIX_ . "lookbook_looks` WHERE `id_cms` = " . $id_cms);
    $obj = new LookbookLooks($id);

    return $obj;
  }
  
  public static function deleteSelectionLook($selection)
	{
		if (!is_array($selection))
			die(Tools::displayError());
		$result = true;
		foreach ($selection AS $id)
		{
		  $look = self::getObjectFromCmsId($id);
		  LookbookLooksProducts::deleteLookProducts($look->id);
			if(!$look->delete())
			  return false;
		}
		return true;
	}
	public static function deleteLooks($id_lookbook)
	{
	  $ids = Db::getInstance()->Execute("SELECT `id_look` FROM `" . _DB_PREFIX_ . "lookbook_looks` WHERE `id_lookbook` = " . $id_lookbook);
	  foreach($ids as $id)
	  {
	    $obj = new LookbookLooks($id);
	    LookbookLooksProducts::deleteLookProducts($obj->id);
	    if(!$obj->delete())
			  return false;
	  }
    return true;
  }
  
  public static function getLooks($id_lookbook_cms)
	{
	  global $cookie;
	  $link = new Link();
    $lookbook = LookbookC::getLookbookFromCmsId($id_lookbook_cms);

	  $sql = "SELECT l.`id_cms`, cl.`link_rewrite`, cl.`meta_title`
	          FROM `" . _DB_PREFIX_ . "lookbook_looks` l
	          INNER JOIN `" . _DB_PREFIX_ . "cms` c ON (c.`id_cms` = l.`id_cms`)
	          INNER JOIN `" . _DB_PREFIX_ . "cms_lang` cl ON (cl.`id_cms` = c.`id_cms`)
	          WHERE cl.`id_lang` = " . (int)$cookie->id_lang . "
	          AND l.`id_lookbook` = " . (int)$lookbook->id_lookbook;

    $results = Db::getInstance()->ExecuteS($sql);

    foreach($results as &$result)
    {
      $result['img']  = _PS_MODULE_DIR_ . 'lookbook/img/' . 'look_' . $result['id_cms'] . '.png';
      $result['link'] = $link->getCMSLink($result['id_cms'], $result['link_rewrite']);
    }
    return $results;
	}
	
	public static function getNextId()
  {
    $results = Db::getInstance()->ExecuteS('SHOW TABLE STATUS FROM ' . _DB_NAME_);
    foreach($results as $result)
    {
      if($result['Name'] == _DB_PREFIX_ . 'lookbook_looks')
        return $result['Auto_increment'] + 1;
    }
  }
}
?>