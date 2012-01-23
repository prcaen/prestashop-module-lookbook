<?php
/**
* 
*/
if(!defined('_PS_VERSION_'))
  exit;

class Lookbook extends Module
{
  public function __construct()
  {
    $this->name = 'lookbook';
		$this->tab = 'front_office_features';
		$this->version = 0.1;
		$this->author = 'Pierrick CAEN';
		$this->need_instance = 0;
		
		parent::__construct();
		
		$this->displayName = $this->l('Lookbook');
		$this->description = $this->l('Manage your lookbook');
		
		$this->_tabs = array(
		  1 => array(
		    'className' => 'AdminLookbook',
		    'i18n'      => array(
		       1 => 'Lookbook',
           2 => 'Lookbook',
           3 => 'Lookbook',
           4 => 'Lookbook',
           5 => 'Lookbook'
		    ),
		    'idParent'  => Tab::getIdFromClassName('AdminCatalog')
		  )
		);
		
		$this->_cmsCategory = array(
		  1 => array(
		    'id_parent'    => 1,
		    'active'       => 1,
		    'link_rewrite' => array(
		      1 => 'lookbook',
          2 => 'lookbook',
          3 => 'lookbook',
          4 => 'lookbook',
          5 => 'lookbook'
		    ),
		    'i18n'         => array(
		      1 => 'Lookbook',
          2 => 'Lookbook',
          3 => 'Lookbook',
          4 => 'Lookbook',
          5 => 'Lookbook'
		    )
		  )
		);
	}
	
	public function install()
  {
    if(!parent::install())
      return false;

    // Set AdminTab
    foreach($this->_tabs AS $tab)
      $this->_installModuleTab($tab['className'], $tab['i18n'], $tab['idParent']);
    
    // Add table lookbook
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook` (`id_look` INT NOT NULL , `timestamp` TIMESTAMP NOT NULL , `offer_nb` INT NOT NULL)"))
      return false;
    // Add table lookbook_products
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook_products` (`id_look` INT NOT NULL , `timestamp` TIMESTAMP NOT NULL , `offer_nb` INT NOT NULL)"))
      return false;
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook_lang` (`id_look` INT NOT NULL , `timestamp` TIMESTAMP NOT NULL , `offer_nb` INT NOT NULL)"))
      return false;
    // Register hook
    //if(!$this->registerHook('lookbook'))
       //die('srezfds');
    // Add CMS Category
    foreach ($this->_cmsCategory AS $category)
    {
      if(!$this->_addCMSCategory($category['id_parent'], $category['active'], $category['i18n'], $category['link_rewrite']))
        return false;
    }

    return true;
  }
  
  public function uninstall()
  {
    if(!parent::uninstall())
      return false;

    // Remove table lookbook
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook`"))
      return false;
    // Remove table lookbook
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook_products`"))
      return false;
    // Remove table lookbook
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook_lang`"))
      return false;
    // Unset AdminTab
    foreach($this->_tabs AS $tab)
      $this->_uninstallModuleTab($tab['className']);
    
    // Remove CMS Category
    foreach ($this->_cmsCategory AS $category)
    {
      if(!$this->_deleteCMSCategory(2, $category['i18n'][2]))
        return false;
    }
      
    return true;
  }

  private function hookLookbook()
	{
		global $smarty;
	
		$smarty->assign('test', 'test');
		return $this->display(__FILE__, 'lookbook.tpl');
	}

  // --- Tools ---
  private function _installModuleTab($className, $name, $idParent)
  {
    $tab = new Tab();

    $tab->class_name = $className;
    $tab->name       = $name;
    $tab->module     = $this->name;
    $tab->id_parent  = $idParent;
    
    if(!$tab->save())
    {
      $this->_errors[] = Tools::displayError('An error occurred while saving new tab: ') . ' <b>' . $tab->name . ' (' . mysql_error() . ')</b>';
      return false;
    }
  }
  
  private function _uninstallModuleTab($className)
  {
    $idTab = Tab::getIdFromClassName($className);

    if($idTab != 0)
    {
      $tab = new Tab($idTab);
      $tab->delete();
      
      return true;
    }
  }

  private function _addCMSCategory($idParent, $active, $name, $linkRewrite)
  {
    $cmsCategory = new CMSCategory();

    $cmsCategory->id_parent     = $idParent;
    $cmsCategory->active        = $active;
    $cmsCategory->name          = $name;
    $cmsCategory->link_rewrite  = $linkRewrite;

    if(!$cmsCategory->save())
    {
      $this->_errors[] = Tools::displayError('An error occurred while saving new the cms category');
      return false;
    }
    else
      return true;
  }
  
  private function _deleteCMSCategory($id_lang, $name)
  {
    $idCMSCateogry = $this->_getIdCMSCategory($id_lang, $name);

    if($idCMSCateogry != 0)
    {
      $cmsCategory = new CMSCategory($idCMSCateogry);
      $cmsCategory->delete();

      return true;
    }
    else
      return false;
  }
  
  private function _getIdCMSCategory($id_lang, $name)
  {
    $idCMSCateogry = CMSCategory::searchByName($id_lang, $name);
    
    return $idCMSCateogry[0]['id_cms_category'];
  }
}