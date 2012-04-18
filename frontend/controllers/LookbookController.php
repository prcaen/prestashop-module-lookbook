<?php
include_once _PS_MODULE_DIR_ . 'lookbook/backend/classes/LookbookObject.php';
include_once _PS_MODULE_DIR_ . 'lookbook/backend/classes/LookObject.php';
class LookbookControllerCore extends FrontController
{
	public $php_self = 'lookbook.php';
	public $tpl_file = 'lookbook.tpl';
	protected $lookbook;

	public function preProcess()
	{
		if($id_lookbook = (int)Tools::getValue('id_lookbook'))
			$this->lookbook = new LookbookObject($id_lookbook, (int)self::$cookie->id_lang, true);
		else
			$this->lookbook = new LookbookObject(1, (int)self::$cookie->id_lang, true);

		if (!Validate::isLoadedObject($this->lookbook))
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
		self::$smarty->assign('lookbook', $this->lookbook);
		self::$smarty->display(_PS_THEME_DIR_. $this->tpl_file);
	}
}
?>