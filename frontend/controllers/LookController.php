<?php
include_once _PS_MODULE_DIR_ . 'lookbook/backend/classes/LookObject.php';
class LookControllerCore extends FrontController
{
	public $php_self = 'look.php';
	public $tpl_file = 'look.tpl';
	protected $look;

	public function preProcess()
	{
		if($id_look = (int)Tools::getValue('id_look'))
			$this->look = new LookObject($id_look, (int)self::$cookie->id_lang);

		if (!Validate::isLoadedObject($this->look))
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
		else
			$this->canonicalRedirection();

		parent::preProcess();
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addJS(_THEME_JS_DIR_.'lookbook.js');
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->assign('look', $this->look);
		self::$smarty->display(_PS_THEME_DIR_. $this->tpl_file);
	}
}
?>