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

  public static function getObjectFromCmsCategoryId($id_cms_category)
  {
    $id = Db::getInstance()->getValue("SELECT `id_lookbook` FROM `" . _DB_PREFIX_ . "lookbook` WHERE `id_cms_category` = " . $id_cms_category);
    $obj = new LookbookC($id);

    return $obj;
  }
  
  public static function getLookbookFromCmsId($id_cms)
  {
    $id = Db::getInstance()->getValue("SELECT `id_lookbook` FROM `" . _DB_PREFIX_ . "lookbook` WHERE `id_cms` = " . $id_cms);
    $lookbook = new LookbookC($id);

    return $lookbook;
  }
  public static function deleteSelectionLookbook($selection)
	{
		if (!is_array($selection))
			die(Tools::displayError());
		$result = true;
		foreach ($selection AS $id)
		{
		  $lookbook = self::getObjectFromCmsId($id);
		  LookbookLooks::deleteSelectionLookFromIdLookbook($lookbook->id);
			if(!$lookbook->delete())
			  return false;
		}
		return true;
	}
	
	public static function getLookbooks()
	{
	  global $cookie;
	  $link = new Link();

	  $sql = "SELECT l.`id_cms`, cl.`link_rewrite`, cl.`meta_title`
	          FROM `" . _DB_PREFIX_ . "lookbook` l
	          INNER JOIN `" . _DB_PREFIX_ . "cms` c ON (c.`id_cms` = l.`id_cms`)
	          INNER JOIN `" . _DB_PREFIX_ . "cms_lang` cl ON (cl.`id_cms` = c.`id_cms`)
	          WHERE cl.`id_lang` = " . (int)$cookie->id_lang;

    $results = Db::getInstance()->ExecuteS($sql);

    foreach($results as &$result)
    {
      $result['img']  = _PS_MODULE_DIR_ . 'lookbook/img/' . 'lookbook_' . $result['id_cms'] . '.png';
      $result['link'] = $link->getCMSLink($result['id_cms'], $result['link_rewrite']);
    }
    return $results;
	}
}
?>