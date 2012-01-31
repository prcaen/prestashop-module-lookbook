<?php
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/tabs/AdminCMSContent.php');
include_once('AdminLookbookProducts.php');

class AdminLookbook extends AdminTab
{
  private $_category;
  private $_id_category;
  private $adminLookbookProducts;

  public function __construct()
	{
	  global $cookie;
	  $this->table = 'cms';
	 	$this->className = 'CMS';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;
	 	
	 	$this->adminLookbookProducts = new AdminLookbookProducts();
	 	
	 	$this->fieldsDisplay = array(
		  'id_cms' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		  'link_rewrite' => array('title' => $this->l('URL'), 'width' => 200),
		  'meta_title' => array('title' => $this->l('Title'), 'width' => 300),
		  'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
		  'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
		);
		$this->_id_category = $this->_getCategoryId((int)$cookie->id_lang, 'Lookbook');

		$this->_category = new CMSCategory($this->_id_category);
		
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'cms_category` c ON (c.`id_cms_category` = a.`id_cms_category`)';
		$this->_select = 'a.position ';
		$this->_filter = 'AND c.id_cms_category = '.(int)($this->_category->id_cms_category);
		
		parent::__construct();
  }
  
  public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		
		if(isset($_GET['addcms']) OR isset($_GET['updatecms']))
		{
		  $this->displayForm($token);
		}
		elseif(isset($_GET['conf']) AND ((int)Tools::getValue('conf') == 4 OR (int)Tools::getValue('conf') == 3))
		  $this->adminLookbookProducts->display($this->token, (int)Tools::getValue('id_cms'));
		else
		{
		  if (($id_cms_category = (int)Tools::getValue('id_cms_category')))
  			$currentIndex .= '&id_cms_category='.$id_cms_category;
  		$this->getList((int)($cookie->id_lang), !$cookie->__get($this->table.'Orderby') ? 'position' : NULL, !$cookie->__get($this->table.'Orderway') ? 'ASC' : NULL);
  		if (!$id_cms_category)
  			$id_cms_category = 1;
  		echo '<h3>'.(!$this->_listTotal ? ($this->l('No pages found')) : ($this->_listTotal.' '.($this->_listTotal > 1 ? $this->l('pages') : $this->l('page')))).' '.
  		$this->l('in category').' "'.stripslashes(CMSCategory::hideCMSCategoryPosition($this->_category->getName())).'"</h3>';
  		echo '<a href="'.$currentIndex.'&id_cms_category='.$id_cms_category.'&add'.$this->table.'&token='.Tools::getAdminTokenLite('AdminLookbook').'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new page').'</a>
  		<div style="margin:10px;">';
  		$this->displayList($token);
  		echo '</div>';
		}
	}
	
	public function displayList($token = NULL)
	{
		global $currentIndex;
		
		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader($token);
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent($token);

		/* Close list table and submit button */
		$this->displayListFooter($token);
	}

  public function displayForm($token)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$divLangName = 'meta_title¤meta_description¤meta_keywords¤ccontent¤link_rewrite';

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.Tools::getAdminTokenLite('AdminLookbook').'" method="post" name="cms" id="cms">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			
			<fieldset><legend><img src="../img/t/AdminLogs.gif" />'.$this->l('Lookbook page').'</legend>';

    // META TITLE
		echo '
		    <input type="hidden" name="id_cms_category" value="'.$this->_id_category.'" />
				<label>'.$this->l('Meta title').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="40" type="text" onkeyup="copyMeta2friendlyURL();" id="name_'.$language['id_lang'].'" name="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_title', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_title');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META DESCRIPTION
		echo '	<label>'.$this->l('Meta description').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_description_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_description', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_description');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// META KEYWORDS
		echo '	<label>'.$this->l('Meta keywords').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="meta_keywords_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_keywords', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'meta_keywords');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		// LINK REWRITE
		echo '	<label>'.$this->l('Friendly URL').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="link_rewrite_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="30" type="text" id="input_link_rewrite_'.$language['id_lang'].'" name="link_rewrite_'.$language['id_lang'].'" onkeyup="this.value = str2url(this.value); updateFriendlyURL();" value="'.htmlentities($this->getFieldValue($obj, 'link_rewrite', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, $divLangName, 'link_rewrite');
		echo '	</div><div class="clear space">&nbsp;</div>';
		
		echo '	<label>'.$this->l('Enable:').' </label>
				<div class="margin-form">
					<input type="radio" name="active" id="active_on" onclick="toggleDraftWarning(false);" value="1" '.($this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" onclick="toggleDraftWarning(true);" value="0" '.(!$this->getFieldValue($obj, 'active') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>';
		// SUBMIT
		echo '	<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset><br />
			
		</form>';
		// TinyMCE
		global $cookie;
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER["PHP_SELF"]);
		echo '
			<script type="text/javascript">	
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			</script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>';
	}
	
  function postProcess()
	{
		global $cookie, $link, $currentIndex;
		
		if (Tools::isSubmit('viewcms') AND ($id_cms = (int)(Tools::getValue('id_cms'))) AND $cms = new CMS($id_cms, (int)($cookie->id_lang)) AND Validate::isLoadedObject($cms))
		{
			$redir = $link->getCMSLink($cms);
			if (!$cms->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
				$redir .= '?adtoken='.Tools::encrypt('PreviewCMS'.$cms->id).'&ad='.$admin_dir;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deletecms'))
		{
			if (Tools::getValue('id_cms') == Configuration::get('PS_CONDITIONS_CMS_ID'))
			{
				Configuration::updateValue('PS_CONDITIONS', 0);
				Configuration::updateValue('PS_CONDITIONS_CMS_ID', 0);
			}
			$cms = new CMS((int)(Tools::getValue('id_cms')));
			$cms->cleanPositions($cms->id_cms_category);
			if (!$cms->delete())
				$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
			else
				Tools::redirectAdmin($currentIndex.'&id_cms_category='.$cms->id_cms_category.'&conf=1&token='.Tools::getAdminTokenLite('AdminLookbook'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$cms = new CMS();
					$result = true;
					$result = $cms->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$cms->cleanPositions((int)(Tools::getValue('id_cms_category')));
						Tools::redirectAdmin($currentIndex.'&conf=2&token='.Tools::getAdminTokenLite('AdminLookbook').'&id_category='.(int)(Tools::getValue('id_cms_category')));
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddcms') OR Tools::isSubmit('submitAddcmsAndPreview'))
		{
			parent::validateRules();

			if (!sizeof($this->_errors))
			{
				if (!$id_cms = (int)(Tools::getValue('id_cms')))
				{
					$cms = new CMS();
					$this->copyFromPost($cms, 'cms');
					if (!$cms->add())
						$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					elseif (Tools::isSubmit('submitAddcmsAndPreview'))
					{
						$preview_url = $link->getCMSLink($cms, $this->getFieldValue($object, 'link_rewrite', $this->_defaultFormLanguage), (int)($cookie->id_lang));
						if (!$cms->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewCMS'.$cms->id);
	
							$preview_url .= $object->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
					 Tools::redirectAdmin($currentIndex.'&id_cms_category='.$cms->id_cms_category. '&id_cms='. $cms->id .'&conf=3&token='.Tools::getAdminTokenLite('AdminLookbook')); 
				}
				else
				{
					$cms = new CMS($id_cms);
					$this->copyFromPost($cms, 'cms');
					if (!$cms->update())
						$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					elseif (Tools::isSubmit('submitAddcmsAndPreview'))
					{
						$preview_url = $link->getCMSLink($cms, $this->getFieldValue($object, 'link_rewrite', $this->_defaultFormLanguage), (int)($cookie->id_lang));
						if (!$cms->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewCMS'.$cms->id);
	
							$preview_url .= $object->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
						Tools::redirectAdmin($currentIndex.'&id_cms_category='.$cms->id_cms_category. '&id_cms='. $cms->id .'&conf=4&token='.Tools::getAdminTokenLite('AdminLookbook'));
				}
			}
		}
		elseif (Tools::getValue('position'))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = $this->loadObject()))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_cms_category'))) ? ('&id_cms_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminLookbook'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('status') AND Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin($currentIndex.'&conf=5'.((int)Tools::getValue('id_cms_category') ? '&id_cms_category='.(int)Tools::getValue('id_cms_category') : '').'&token='.Tools::getValue('token'));
					else
						$this->_errors[] = Tools::displayError('An error occurred while updating status.');
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		else
			parent::postProcess(true);
	}

	private function _getCategoryId($id_lang, $name)
  {
    $category = CMSCategory::searchByName($id_lang, $name);
    
    return $category[0]['id_cms_category'];
  }
}
?>