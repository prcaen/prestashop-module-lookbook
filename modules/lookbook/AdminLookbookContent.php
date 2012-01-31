<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7541 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/../modules/lookbook/lookbook.php');
include_once('classes/AdminLookbook.php');
include_once('classes/AdminLookbookCategories.php');

class AdminLookbookContent extends AdminTab
{
	/** @var object adminCMSCategories() instance */
	private $adminLookbookCategories;

	/** @var object adminCMS() instance */
	private $adminLookbook;

	/** @var object Category() instance for navigation*/
	private static $_category = NULL;
	
	private $lookbook_root_id;

	public function __construct()
	{
	  global $cookie;

		/* Get current category */
		$this->lookbook_root_id = Configuration::get(PS_LOOKBOOK_CATEGORY_ID);
		$id_cms_category = (int)(Tools::getValue('id_cms_category', Tools::getValue('id_cms_category_parent', $this->lookbook_root_id)));

		self::$_category = new CMSCategory($id_cms_category);
		if (!Validate::isLoadedObject(self::$_category))
			die('Category cannot be loaded');

		$this->table = array('cms_category', 'cms');
		$this->adminLookbookCategories = new AdminLookbookCategories();
		$this->adminLookbook = new AdminLookbook();

		parent::__construct();
	}

	/**
	 * Return current category
	 *
	 * @return object
	 */
	public static function getCurrentLookbookCategory()
	{
		return self::$_category;
	}

	public function viewAccess($disable = false)
	{
		$result = parent::viewAccess($disable);
		$this->adminLookbookCategories->tabAccess = $this->tabAccess;
		$this->adminLookbook->tabAccess = $this->tabAccess;
		return $result;
	}

	public function postProcess()
	{
	  global $cookie;
		if (Tools::isSubmit('submitDelcms') OR Tools::isSubmit('previewSubmitAddcmsAndPreview') OR Tools::isSubmit('submitAddcms') OR isset($_GET['deletecms']) OR Tools::isSubmit('viewcms') OR (Tools::isSubmit('statuscms') AND Tools::isSubmit('id_cms')) OR (Tools::isSubmit('position') AND !Tools::isSubmit('id_cms_category_to_move')))
			$this->adminLookbook->postProcess();
		if (Tools::isSubmit('submitDelcms_category') OR Tools::isSubmit('submitAddcms_categoryAndBackToParent') OR Tools::isSubmit('submitAddcms_category') OR isset($_GET['deletecms_category']) OR (Tools::isSubmit('statuscms_category') AND Tools::isSubmit('id_cms_category')) OR (Tools::isSubmit('position') AND Tools::isSubmit('id_cms_category_to_move')))
			$this->adminLookbookCategories->postProcess();
	}

	public function displayErrors()
	{
		parent::displayErrors();
		$this->adminLookbook->displayErrors();
		$this->adminLookbookCategories->displayErrors();
	}

	public function display()
	{
		global $currentIndex, $cookie;

    echo '<div class="conf warn"><img src="../img/admin/warning.gif" alt="warn">' . $this->l('To display the lookbook page, in the cms.tpl page, please add at line 26: ') . '<pre>{if isset($lookbook)}
    {$HOOK_LOOKBOOK}
{else}</pre>' . $this->l('and line 77: '). '<pre>{/if}</pre></div>';
		if (((Tools::isSubmit('submitAddcms_category') OR Tools::isSubmit('submitAddcms_categoryAndStay')) AND sizeof($this->adminLookbookCategories->_errors)) OR isset($_GET['updatecms_category']) OR isset($_GET['addcms_category']))
		{
			$this->adminLookbookCategories->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
			
		}
		elseif (((Tools::isSubmit('submitAddcms') OR Tools::isSubmit('submitAddcmsAndStay')) AND sizeof($this->adminLookbook->_errors)) OR isset($_GET['updatecms']) OR isset($_GET['addcms']))
		{
			$this->adminLookbook->displayForm($this->token);
			echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
		
		}
		else
		{
		  $id_cms_category = (int)(Tools::getValue('id_cms_category'));
		  if (!$id_cms_category)
		  	$id_cms_category = $this->lookbook_root_id;
		  $cms_tabs = array('cms_category', 'cms');
		  // Cleaning links
		  $catBarIndex = $currentIndex;
		  foreach ($cms_tabs AS $tab)
		  	if (Tools::getValue($tab.'Orderby') && Tools::getValue($tab.'Orderway')) 
		  		$catBarIndex = preg_replace('/&'.$tab.'Orderby=([a-z _]*)&'.$tab.'Orderway=([a-z]*)/i', '', $currentIndex);
		  echo '<div class="cat_bar"><span style="color: #3C8534;">'.$this->l('Current category').' :</span>&nbsp;&nbsp;&nbsp;'. $this->getPath($catBarIndex, $id_cms_category, $this->lookbook_root_id,'','','cms').'</div>';
		  
		  if($id_cms_category == $this->lookbook_root_id  || self::$_category->id_parent == $this->lookbook_root_id)
		  {
		    if($id_cms_category == $this->lookbook_root_id)
		      echo '<h2>'.$this->l('Lookbooks').'</h2>';
		    elseif(self::$_category->id_parent == $this->lookbook_root_id)
		      echo '<h2>'.$this->l('Looks').'</h2>';
		    else
		      echo '<h2>'.$this->l('Categories').'</h2>';
  		  $this->adminLookbookCategories->display($this->token);
  		  echo '<div style="margin:10px">&nbsp;</div>';
		  }
		  if($id_cms_category == $this->lookbook_root_id  || self::$_category->id_parent == $this->lookbook_root_id)
		    echo '<h2>'.$this->l('Page').'</h2>';
		  else
		    echo '<h2>'.$this->l('Pages in this look').'</h2>';
  		  $this->adminLookbook->display($this->token);
		}	
	}

  // --- Tools ---
  private function getPath($urlBase, $id_category,$id_top, $path = '', $highlight = '')
  {
  	global $cookie;

  		$category = new CMSCategory($id_category, (int)($cookie->id_lang));
  		if (!$category->id)
  			return $path;

  		$name = ($highlight != NULL) ? str_ireplace($highlight, '<span class="highlight">'.$highlight.'</span>', CMSCategory::hideCMSCategoryPosition($category->name)) : CMSCategory::hideCMSCategoryPosition($category->name);
  		$edit = '<a href="'.$urlBase.'&id_cms_category='.$category->id.'&addcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
  				<img src="../img/admin/edit.gif" alt="Modify" /></a> ';
  		if ($category->id == $id_top)
  			$edit = '<a href="'.$urlBase.'&id_cms_category='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
  					<img src="../img/admin/home.gif" alt="Home" /></a> ';
  		$path = $edit.'<a href="'.$urlBase.'&id_cms_category='.$category->id.'&viewcategory&token=' . Tools::getAdminToken('AdminLookbookContent'.(int)(Tab::getIdFromClassName('AdminLookbookContent')).(int)($cookie->id_employee)).'">
  		'.$name.'</a> > '.$path;
  		if ($category->id == $id_top)
  			return substr($path, 0, strlen($path) - 3);
  		return $this->getPath($urlBase, $category->id_parent, $id_top, $path, '', 'cms');
  }
}

