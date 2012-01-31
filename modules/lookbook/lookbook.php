<?php

if(!defined('_PS_VERSION_'))
  exit;

include_once(_PS_MODULE_DIR_.'lookbook/classes/Lookbook.php');
define('PS_LOOKBOOK_CATEGORY_ID', 'PS_LOOKBOOK_CMS_CATEGORY_ID');
define('PS_LOOKBOOK_CMS_ID', 'PS_LOOKBOOK_CMS_ID');

class Lookbook extends Module
{
  private $_tabs        = array();
  private $_cms         = array();
  private $_cmsCategory = array();

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

    $this->_tabs[] = array(
      'className' => 'AdminLookbookContent',
      'i18n'      => array(
        1 => 'Lookbook',
        2 => 'Lookbook',
        3 => 'Lookbook',
        4 => 'Lookbook',
        5 => 'Lookbook'
      ),
      'idParent' => Tab::getIdFromClassName('AdminCatalog')
    );

    $this->_cmsCategory[] = array(
      'id_parent'    => 1,
      'active'       => 1,
      'link_rewrite' => array(
        1 => 'lookbooks',
        2 => 'lookbooks',
        3 => 'lookbooks',
        4 => 'lookbooks',
        5 => 'lookbooks'
      ),
      'i18n'         => array(
        1 => 'Lookbooks',
        2 => 'Lookbooks',
        3 => 'Lookbooks',
        4 => 'Lookbooks',
        5 => 'Lookbooks'
      ),
      'root' => true
    );
    
    $this->_cms[] = array(
      'id_cms_category' => 1, // temp
      'active'          => 1,
      'meta_title'      => array(
        1 => 'Lookbook',
        2 => 'Lookbook',
        3 => 'Lookbook',
        4 => 'Lookbook',
        5 => 'Lookbook'
      ),
      'link_rewrite'    => array(
        1 => 'lookbook',
        2 => 'lookbook',
        3 => 'lookbook',
        4 => 'lookbook',
        5 => 'lookbook'
      ),
      'meta_description'      => array(
        1 => 'Lookbook',
        2 => 'Lookbook',
        3 => 'Lookbook',
        4 => 'Lookbook',
        5 => 'Lookbook'
      ),
      'meta_keywords'         => array(
        1 => 'lookbook',
        2 => 'lookbook',
        3 => 'lookbook',
        4 => 'lookbook',
        5 => 'lookbook'
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
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook` (`id_lookbook` INT NOT NULL AUTO_INCREMENT, `id_cms` INT NOT NULL, `id_cms_category` INT NOT NULL, PRIMARY KEY (`id_lookbook`))"))
      return false;
    // Add table lookbook_lang
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook_lang` (`id_lookbook_lang` INT NOT NULL AUTO_INCREMENT, `id_lookbook` INT NOT NULL, `content` LONGTEXT NOT NULL, PRIMARY KEY (`id_lookbook_lang`))"))
      return false;
    // Add table lookbook_looks
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook_looks` (`id_look` INT NOT NULL AUTO_INCREMENT, `id_lookbook` INT NOT NULL, `id_cms` INT NOT NULL, PRIMARY KEY (`id_look`))"))
      return false;
    // Add table lookbook_looks_products
    if(!Db::getInstance()->Execute("CREATE TABLE `" . _DB_PREFIX_ . "lookbook_looks_products` (`id_lookbook_looks_product` INT NOT NULL AUTO_INCREMENT, `id_look` INT NOT NULL, `id_product` INT NOT NULL, PRIMARY KEY (`id_lookbook_looks_product`))"))
      return false;

    // Insert hook
    if(!DB::getInstance()->Execute("INSERT INTO `"._DB_PREFIX_."hook` SET `name`= 'cmsLookbook', `title`= 'Lookbook', `description`= 'Lookbook hook'"))
      return false;

    // Register hook
    if(!$this->registerHook('header') || !$this->registerHook('cmsLookbook'))
      return false;

    // Add CMS Category
    foreach ($this->_cmsCategory AS $category)
    {
      if(!self::addCMSCategory($category['id_parent'], $category['active'], $category['i18n'], $category['link_rewrite'], $category['root']))
        return false;
    }
    $this->_cms[0]['id_cms_category'] = Configuration::get(PS_LOOKBOOK_CATEGORY_ID);

    // Add CMS
    foreach($this->_cms AS $cms)
    {
      if(!self::addCMS($cms['id_cms_category'], $cms['meta_title'], $cms['link_rewrite'], $cms['active'], $cms['meta_description'], $cms['meta_keywords'], true))
        return false;
    }
    
    // Add lookbook root
    $lookbook = new LookbookC();

    $lookbook->id_cms          = Configuration::get(PS_LOOKBOOK_CMS_ID);
    $lookbook->id_cms_category = Configuration::get(PS_LOOKBOOK_CATEGORY_ID);
    
    if(!$lookbook->save())
      return false;

    return true;
  }

  public function uninstall()
  {
    if(!parent::uninstall())
      return false;

    // Unset AdminTab
    foreach($this->_tabs AS $tab)
      $this->_uninstallModuleTab($tab['className']);

    // Remove table lookbook
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook`"))
      return false;
    // Remove table lookbook
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook_looks`"))
      return false;
    // Remove table lookbook_looks_products
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook_looks_products`"))
      return false;
    // Remove table lookbook_lang
    if(!DB::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "lookbook_lang`"))
      return false;

    // Delete hook
    if(!DB::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook` WHERE `hook`.`name` = 'cmsLookbook'"))
      return false;

    // Remove CMS Category
    if(!self::deleteCMSCategory(Configuration::get(PS_LOOKBOOK_CATEGORY_ID), true))
      return false;

    return true;
  }

  function hookHeader($params)
  {
    global $smarty;
    $smarty->assign(array('HOOK_LOOKBOOK' => Module::hookExec('cmsLookbook')));

    // Add media to header
    Tools::addCSS(($this->_path) . 'lookbook.css', 'all');
    Tools::addJS(($this->_path)  . 'lookbook.js');
  }

  public function hookCmsLookbook($params)
  {
    global $smarty;
    $smarty->assign('lookbook', true);
    $smarty->assign('lookbook_page_type', $this->getCategoryLvl(Tools::getValue('id_cms')));
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

  public static function addCMSCategory($idParent, $active, $name, $linkRewrite, $root = false)
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
    {
      if($root)
        Configuration::updateValue(PS_LOOKBOOK_CATEGORY_ID, $cmsCategory->id);

      return true;
    }
  }
  
  public static function deleteCMSCategory($id_cms_category, $root = false)
  {
    if($id_cms_category != 0)
    {
      $cmsCategory = new CMSCategory($id_cms_category);
      $cmsCategory->delete();
      if($root)
        Configuration::deleteByName(PS_LOOKBOOK_CATEGORY_ID);

      return true;
    }
    else
      return false;
  }

  public static function addCMS($category, $meta_title, $link_rewrite, $active = true, $meta_description = NULL, $meta_keywords = NULL, $root = false)
  {
    $cms = new CMS();

    $cms->id_cms_category  = $category;
    $cms->meta_title       = $meta_title;
    $cms->link_rewrite     = $link_rewrite;
    $cms->active           = $active;
    $cms->meta_description = $meta_description;
    $cms->meta_keywords    = $meta_keywords;

    if(!$cms->save())
    {
      $this->_errors[] = Tools::displayError('An error occurred while saving new the cms category');
      return false;
    }
    else
    {
      if($root)
        Configuration::updateValue(PS_LOOKBOOK_CMS_ID, $cms->id);

      return true;
    }
      
  }

  public static function editCMS($idCms, $meta_title = NULL, $link_rewrite = NULL, $meta_description = NULL, $meta_keywords = NULL)
  {
    $cms = new CMS($idCms);
  }

  public static function removeCMS($id_lang, $id_cms_category)
  {
    $idCMS = CMS::getCMSPages($id_lang, $id_cms_category);
    
    if($cms !=0)
    {
      $cms = new CMS($idCMS);
      $cms->delete();

      return false;
    }
    else
      return false;
  }
  
  private function getCategoryLvl($id)
  {
    if($id == Configuration::get(PS_LOOKBOOK_CMS_ID))
      return 0;
    elseif(Db::getInstance()->getValue("SELECT COUNT(`id_look`) FROM `lookbook_looks` WHERE  `" . _DB_PREFIX_ . "id_cms` = " . $id) != 0)
      return 1;
    else
      return 2;
  }
}