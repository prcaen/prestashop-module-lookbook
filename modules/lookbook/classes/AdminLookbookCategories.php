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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
include_once(PS_ADMIN_DIR.'/../modules/lookbook/classes/Lookbook.php');

class AdminLookbookCategories extends AdminTab
{
	protected $maxImageSize = 300000;
	private $lookbook_root_id;

	/** @var object CMSCategory() instance for navigation*/
	private $_CMSCategory;

	public function __construct()
	{
		global $cookie;
		
		$this->table = 'cms_category';
	 	$this->className = 'CMSCategory';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;


		$this->fieldsDisplay = array(
		'id_cms_category' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 30),
		'name' => array('title' => $this->l('Name'), 'width' => 100, 'callback' => 'hideCMSCategoryPosition'),
		'description' => array('title' => $this->l('Description'), 'width' => 500, 'maxlength' => 90, 'orderby' => false),
		'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
		'active' => array('title' => $this->l('Displayed'), 'active' => 'status', 'align' => 'center', 'type' => 'bool', 'orderby' => false));
		
		$this->_CMSCategory = AdminLookbookContent::getCurrentLookbookCategory();
		$this->_filter = 'AND `id_parent` = '.(int)($this->_CMSCategory->id);
		$this->_select = 'position ';

    $this->lookbook_root_id = Configuration::get(PS_LOOKBOOK_CATEGORY_ID);
		parent::__construct();
		
		$this->_looks = array(
		  'id_parent'    => 1, // temp
		  'active'       => 1,
		  'link_rewrite' => array(
		    1 => 'looks',
        2 => 'looks',
        3 => 'looks',
        4 => 'looks',
        5 => 'looks'
		  ),
		  'i18n'         => array(
		    1 => 'Looks',
        2 => 'Looks',
        3 => 'Looks',
        4 => 'Looks',
        5 => 'Looks'
		  )
		);
		
		$this->_look = array(
      'id_cms_category' => 1, // temp
      'active'          => 1,
	    'meta_title'      => array(
	      1 => 'Looks',
        2 => 'Looks',
        3 => 'Looks',
        4 => 'Looks',
        5 => 'Looks'
	    ),
	    'link_rewrite'    => array(
	      1 => 'looks',
        2 => 'looks',
        3 => 'looks',
        4 => 'looks',
        5 => 'looks'
	    ),
	    'meta_description'      => array(
	      1 => 'looks',
        2 => 'looks',
        3 => 'looks',
        4 => 'looks',
        5 => 'looks'
	    ),
	    'meta_keywords'         => array(
	      1 => 'looks',
        2 => 'looks',
        3 => 'looks',
        4 => 'looks',
        5 => 'looks'
	    )
    );
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

	public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		$id_cms_category = (int)(Tools::getValue('id_cms_category', 1));

		$this->getList((int)($cookie->id_lang), !$cookie->__get($this->table.'Orderby') ? 'position' : NULL, !$cookie->__get($this->table.'Orderway') ? 'ASC' : NULL);
		
		//echo '<h3>'.(!$this->_listTotal ? ($this->l('There are no subcategories')) : ($this->_listTotal.' '.($this->_listTotal > 1 ? $this->l('subcategories') : $this->l('subCMS Category')))).' '.$this->l('in CMS Category').' "'.stripslashes(CMSCategory::hideCMSCategoryPosition($this->_CMSCategory->getName())).'"</h3>';
		if($this->_CMSCategory->id_cms_category == $this->lookbook_root_id)
		  echo '<a href="'.__PS_BASE_URI__.substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__)).'?tab=AdminLookbookContent&add'.$this->table.'&id_parent='.Tools::getValue('id_cms_category').'&token='.($token!=NULL ? $token : $this->token).'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new lookbook').'</a>
		  <div style="margin:10px;">';
		else
		  echo '<div style="margin:10px;">';
		
		if($this->_CMSCategory->id_cms_category != $this->lookbook_root_id)
		{
		  $this->delete = false;
		  unset($this->fieldsDisplay['active']);
		}
		$this->displayList($token);
		echo '</div>';
	}

	public function postProcess($token = NULL)
	{
		global $cookie, $currentIndex;

		$this->tabAccess = Profile::getProfileAccess($cookie->profile, $this->id);
		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;
		
		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			
			if ($id_cms_category = (int)(Tools::getValue('id_cms_category')))
			{
				if (!CMSCategory::checkBeforeMove($id_cms_category, (int)(Tools::getValue('id_parent'))))
				{
					$this->_errors[] = Tools::displayError('CMS Category cannot be moved here');
					return false;
				}
			}
			
			/* Checking fields validity */
			$this->validateRules();
			if (!sizeof($this->_errors))
			{
				$id = (int)(Tools::getValue($this->identifier));

				/* Object update */
				if (isset($id) AND !empty($id))
				{
					if ($this->tabAccess['edit'] === '1' OR ($this->table == 'employee' AND $cookie->id_employee == Tools::getValue('id_employee') AND Tools::isSubmit('updateemployee')))
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							/* Specific to objects which must not be deleted */
							if ($this->deleted AND $this->beforeDelete($object))
							{
								// Create new one with old objet values
								$objectNew = new $this->className($object->id);
								$objectNew->id = NULL;
								$objectNew->date_add = '';
								$objectNew->date_upd = '';

								// Update old object to deleted
								$object->deleted = 1;
								$object->update();

								// Update new object with post values
								$this->copyFromPost($objectNew, $this->table);
								$result = $objectNew->add();
								if (Validate::isLoadedObject($objectNew))
									$this->afterDelete($objectNew, $object->id);
							}
							else
							{
								$this->copyFromPost($object, $this->table);
								$result = $object->update();
								$this->afterUpdate($object);
							}
							if (!$result)
								$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.'</b> ('.Db::getInstance()->getMsgError().')';
							elseif ($this->postImage($object->id) AND !sizeof($this->_errors))
							{
								$parent_id = (int)(Tools::getValue('id_parent', 1));
								// Specific back redirect
								if ($back = Tools::getValue('back'))
									Tools::redirectAdmin(urldecode($back).'&conf=4');
								// Specific scene feature
								if (Tools::getValue('stay_here') == 'on' || Tools::getValue('stay_here') == 'true' || Tools::getValue('stay_here') == '1')
									Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&updatescene&token='.$token);
								// Save and stay on same form
								if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
									Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=4&update'.$this->table.'&token='.$token);
								// Save and back to parent
								if (Tools::isSubmit('submitAdd'.$this->table.'AndBackToParent'))
									Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$parent_id.'&conf=4&token='.$token);
								// Default behavior (save and back)
								Tools::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=4&token='.$token);
							}
						}
						else
							$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
				}

				/* Object creation */
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						$object = new $this->className();
						$this->copyFromPost($object, $this->table);
						if (!$object->add())
							$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
						elseif (($_POST[$this->identifier] = $object->id /* voluntary */) AND $this->postImage($object->id) AND !sizeof($this->_errors) AND $this->_redirect)
						{
							$parent_id = (int)(Tools::getValue('id_parent', 1));
							$this->afterAdd($object);
							
							// Add looks
							if($parent_id == $this->lookbook_root_id)
        		  {
        		    $this->_looks['id_parent'] = $this->_look['id_cms_category'] = $object->id;

        		    $cms = new CMS();
        		    $cmsCategory = new CMSCategory();

                $cms->id_cms_category  = $this->_look['id_cms_category'];
                $cms->meta_title       = $object->name;
                $cms->link_rewrite     = $object->link_rewrite;
                $cms->active           = $this->_look['active'];
                $cms->meta_description = $object->meta_description;
                $cms->meta_keywords    = $object->meta_keywords;

                $cmsCategory->id_parent     = $this->_looks['id_parent'];
                $cmsCategory->active        = $this->_looks['active'];
                $cmsCategory->name          = $this->_looks['i18n'];
                $cmsCategory->link_rewrite  = $this->_looks['link_rewrite'];

                if($cms->save() && $cmsCategory->save())
                {
                  $lookbook = new LookbookC();

                  $lookbook->id_cms          = $cms->id;
                  $lookbook->id_cms_category = $cmsCategory->id;

                  if(!$lookbook->save())
                    $this->_errors[] = Tools::displayError('An error occurred while saving the new lookbook');
                }
                else
                  $this->_errors[] = Tools::displayError('An error occurred while saving the new cms and the new cms category');
        		  }
							// Save and stay on same form
							if (Tools::isSubmit('submitAdd'.$this->table.'AndStay'))
								Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$object->id.'&conf=3&update'.$this->table.'&token='.$token);
							// Save and back to parent
							if (Tools::isSubmit('submitAdd'.$this->table.'AndBackToParent'))
								Tools::redirectAdmin($currentIndex.'&'.$this->identifier.'='.$parent_id.'&conf=3&token='.$token);
							// Default behavior (save and back)
							Tools::redirectAdmin($currentIndex.($parent_id ? '&'.$this->identifier.'='.$object->id : '').'&conf=3&token='.$token);
						}
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to add here.');
				}
			}
			$this->_errors = array_unique($this->_errors);
		}
		/* Change object statuts (active, inactive) */
		elseif (isset($_GET['statuscms_category']) AND Tools::getValue($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin($currentIndex.'&conf=5'.((int)$object->id_parent ? '&id_cms_category='.(int)$object->id_parent : '').'&token='.Tools::getValue('token'));
					else
						$this->_errors[] = Tools::displayError('An error occurred while updating status.');
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
		}
		/* Delete object */
		elseif (isset($_GET['delete'.$this->table]))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()) AND isset($this->fieldImageSettings))
				{
					// check if request at least one object with noZeroObject
					if (isset($object->noZeroObject) AND sizeof($taxes = call_user_func(array($this->className, $object->noZeroObject))) <= 1)
						$this->_errors[] = Tools::displayError('You need at least one object.').' <b>'.$this->table.'</b><br />'.Tools::displayError('You cannot delete all of the items.');
					else
					{
						$this->deleteImage($object->id);
						if ($this->deleted)
						{
							$object->deleted = 1;
							if ($object->update())
							{
							  $lookbook = new LookbookC($object->id);
							  $lookbook->delete();
								Tools::redirectAdmin($currentIndex.'&conf=1&token='.Tools::getValue('token'));
							}
						}
						elseif ($object->delete())
						{
						  $lookbook = new LookbookC($object->id);
						  $lookbook->delete();

							Tools::redirectAdmin($currentIndex.'&conf=1&token='.Tools::getValue('token'));
						}
						$this->_errors[] = Tools::displayError('An error occurred during deletion.');
					}
				}
				else
					$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (isset($_GET['position']))
		{
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = new CMSCategory((int)(Tools::getValue($this->identifier, Tools::getValue('id_cms_category_to_move', 1))))))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.(($id_category = (int)(Tools::getValue($this->identifier, Tools::getValue('id_cms_category_parent', 1)))) ? ('&'.$this->identifier.'='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminLookbookContent'));
		}
		/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$cms_category = new CMSCategory();
					$result = true;
					$result = $cms_category->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$cms_category->cleanPositions((int)(Tools::getValue('id_cms_category')));
						Tools::redirectAdmin($currentIndex.'&conf=2&token='.Tools::getAdminTokenLite('AdminLookbookContent').'&id_category='.(int)(Tools::getValue('id_cms_category')));
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		parent::postProcess(true);
	}

	public function displayForm($token=NULL)
	{
		global $currentIndex, $cookie;
		parent::displayForm();

		if (!($obj = $this->loadObject(true)))
			return;
		$active = $this->getFieldValue($obj, 'active');

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token!=NULL ? $token : $this->token).'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset style="width:520px"><legend><img src="../img/admin/tab-categories.gif" />'.$this->l('CMS Category').'</legend>
				<label '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>'.$this->l('Name:').' </label>
				<div class="margin-form translatable" '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>';
		foreach ($this->_languages as $language)
			echo '
					<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" style="width: 260px" name="name_'.$language['id_lang'].'" id="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" '.((!$obj->id) ? ' onkeyup="copy2friendlyURL();"' : '').' /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				<label '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>'.$this->l('Displayed:').' </label>
				<div class="margin-form" '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>
					<input type="radio" name="active" id="active_on" value="1" '.($active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="active" id="active_off" value="0" '.(!$active ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
				</div>
				<label>'.$this->l('Parent CMS Category:').' </label>
				<div class="margin-form">
					<select name="id_parent">';
		$categories = CMSCategory::getCategories((int)($cookie->id_lang), false);
		CMSCategory::recurseCMSCategory($categories, $this->_getLookbookCategory(), $this->lookbook_root_id, $this->getFieldValue($obj, 'id_parent'));
		echo '
					</select>
				</div>
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages as $language)
			echo '
					<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'" rows="5" cols="40">'.htmlentities($this->getFieldValue($obj, 'description', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				<div class="clear"><br /></div>	
				<label>'.$this->l('Meta title:').' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages as $language)
			echo '
					<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_title_'.$language['id_lang'].'" id="meta_title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_title', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				<label>'.$this->l('Meta description:').' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages as $language)
			echo '<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_description_'.$language['id_lang'].'" id="meta_description_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_description', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
				</div>';
		echo '	<p class="clear"></p>
				</div>
				<label>'.$this->l('Meta keywords:').' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages as $language)
			echo '
					<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="meta_keywords_'.$language['id_lang'].'" id="meta_keywords_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'meta_keywords', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<span class="hint" name="help_box">'.$this->l('Forbidden characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				<label '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>'.$this->l('Friendly URL:').' </label>
				<div class="margin-form translatable" '.($this->_CMSCategory->id_parent != $this->lookbook_root_id && isset($_GET['id_cms_category']) ? 'style="display:none"' : '') .'>';
		foreach ($this->_languages as $language)
			echo '<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input type="text" name="link_rewrite_'.$language['id_lang'].'" id="link_rewrite_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'link_rewrite', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" onkeyup="this.value = str2url(this.value);" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Only letters and the minus (-) character are allowed').'<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				
				<div class="margin-form">
					<input type="submit" value="'.$this->l('Save and back to parent CMS Category').'" name="submitAdd'.$this->table.'AndBackToParent" class="button" />
					&nbsp;<input type="submit" class="button" name="submitAdd'.$this->table.'" value="'.$this->l('Save').'"/>
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>
		<p class="clear"></p>';
	}
  
  private function _getLookbookCategory($active = true, $order = true)
  {
    global $cookie;
    
    $lookbook_category = $this->lookbook_root_id;
    
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'cms_category` c
		LEFT JOIN `'._DB_PREFIX_.'cms_category_lang` cl ON c.`id_cms_category` = cl.`id_cms_category`
		WHERE `id_lang` = '.(int)$cookie->id_lang.'
		'.($active ? 'AND `active` = 1' : '').'
		AND c.`id_cms_category` ='.$lookbook_category.'
		ORDER BY `name` ASC');

		if (!$order)
			return $result;

		$categories = array();
		foreach ($result AS $row)
			$categories[$row['id_parent']][$row['id_cms_category']]['infos'] = $row;
		return $categories[1][$lookbook_category];
  }
}
