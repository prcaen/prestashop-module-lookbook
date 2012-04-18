<?php
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include(_PS_MODULE_DIR_.'lookbook/AdminLookbook.php');
include(_PS_MODULE_DIR_.'lookbook/AdminLook.php');
include(_PS_MODULE_DIR_.'lookbook/backend/classes/LookbookObject.php');
include(_PS_MODULE_DIR_.'lookbook/backend/classes/LookObject.php');

class AdminLookbookContent extends AdminTab
{
	/** @var object adminLookbooks() instance */
	private $adminLookbooks;

	/** @var object adminLook() instance */
	private $adminLook;

	/** @var object Category() instance for navigation*/
	private static $_category = NULL;

	public function __construct()
	{
		/* Get current category */
		$id_lookbook = (int)(Tools::getValue('id_lookbook', Tools::getValue('id_lookbook_parent', 1)));
		self::$_category = new LookbookObject($id_lookbook);
		if (!Validate::isLoadedObject(self::$_category))
			die('Category cannot be loaded');

		$this->table = array('lookbook', 'look');
		$this->adminLookbooks = new AdminLookbook();
		$this->adminLook = new AdminLook();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentLookbook()
	{
		return self::$_category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminLookbooks->tabAccess = $this->tabAccess;
		$this->adminLook->tabAccess = $this->tabAccess;
		return $result;
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitDellook') OR Tools::isSubmit('previewSubmitAddlookAndPreview') OR Tools::isSubmit('submitAddlook') OR isset($_GET['deletelook']) OR Tools::isSubmit('viewlook') OR (Tools::isSubmit('statuslook') AND Tools::isSubmit('id_look')) OR (Tools::isSubmit('position') AND !Tools::isSubmit('id_lookbook_to_move')))
			$this->adminLook->postProcess();
		if (Tools::isSubmit('submitDellookbook') OR Tools::isSubmit('submitAddlookbookAndBackToParent') OR Tools::isSubmit('submitAddlookbook') OR isset($_GET['deletelookbook']) OR (Tools::isSubmit('statuslookbook') AND Tools::isSubmit('id_lookbook')) OR (Tools::isSubmit('position') AND Tools::isSubmit('id_lookbook_to_move')))
			$this->adminLookbooks->postProcess();

	}

	public function displayErrors()
	{
		parent::displayErrors();
		$this->adminLook->displayErrors();
		$this->adminLookbooks->displayErrors();
	}

	public function display()
	{
		global $currentIndex;

		if (((Tools::isSubmit('submitAddlookbook') OR Tools::isSubmit('submitAddlookbookAndStay')) AND sizeof($this->adminLookbooks->_errors)) OR isset($_GET['updatelookbook']) OR isset($_GET['addlookbook']))
		{
			$this->adminLookbooks->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
			
		}
		elseif (((Tools::isSubmit('submitAddlook') OR Tools::isSubmit('submitAddlookAndStay')) AND sizeof($this->adminLook->_errors)) OR isset($_GET['updatelook']) OR isset($_GET['addlook']))
		{
			$this->adminLook->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		
		}
		else
		{
		$id_lookbook = (int)(Tools::getValue('id_lookbook'));
		if (!$id_lookbook)
			$id_lookbook = 1;
		$look_tabs = array('lookbook', 'look');
		// Cleaning links
		$catBarIndex = $currentIndex;
		foreach ($look_tabs AS $tab)
			if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway')) 
				$catBarIndex = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', $currentIndex);
		
		echo '<div class="cat_bar"><span style="color: #3C8534;">'.$this->l('Current lookbook').' :</span>&nbsp;&nbsp;&nbsp;'.$this->getPath($catBarIndex, $id_lookbook,'','','look').'</div>';
		echo '<h2>'.$this->l('Lookbooks').'</h2>';
		$this->adminLookbooks->display($this->token);
		echo '<div style="margin:10px">&nbsp;</div>';
		echo '<h2>'.$this->l('Looks in this lookbook').'</h2>';
		$this->adminLook->display($this->token);
		}
		
	}

	private function getPath($urlBase, $id_lookbook, $path = '', $highlight = '')
	{
		global $cookie;

		$category = new LookbookObject($id_lookbook, (int)($cookie->id_lang));
		if (!$category->id)
			return $path;

		$name = ($highlight != NULL) ? str_ireplace($highlight, '<span class="highlight">'.$highlight.'</span>', LookbookObject::hideLookbookPosition($category->name)) : LookbookObject::hideLookbookPosition($category->name);
		$edit = '<a href="'.$urlBase.'&id_lookbook='.$category->id.'&addcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
				<img src="../img/admin/edit.gif" alt="Modify" /></a> ';
		if ($category->id == 1)
			$edit = '<a href="'.$urlBase.'&id_lookbook='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
					<img src="../img/admin/home.gif" alt="Home" /></a> ';
		$path = $edit.'<a href="'.$urlBase.'&id_lookbook='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
		'.$name.'</a> > '.$path;
		if ($category->id == 1)
			return substr($path, 0, strlen($path) - 3);
		return $this->getPath($urlBase, $category->id_parent, $path, '');
	}
}

