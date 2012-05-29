<?php
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminLook extends AdminTab
{	
	private $_category;
	protected $identifiersDnd = array('id_look' => 'id_look', 'id_lookbook' => 'id_lookbook_to_move');

	public function __construct()
	{
	 	$this->table = 'look';
	 	$this->className = 'LookObject';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->view = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
			'id_look' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'link_rewrite' => array('title' => $this->l('URL'), 'width' => 200),
			'meta_title' => array('title' => $this->l('Title'), 'width' => 300),
			//'position' => array('title' => $this->l('Position'), 'width' => 40,'filter_key' => 'position', 'align' => 'center', 'position' => 'position'),
			'active' => array('title' => $this->l('Enabled'), 'width' => 25, 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
			);
			
		$this->_category = AdminLookbookContent::getCurrentLookbook();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'lookbook` l ON (l.`id_lookbook` = a.`id_lookbook`)';
		$this->_select = 'a.position ';
		$this->_filter = 'AND l.id_lookbook = '.(int)($this->_category->id);
		
		parent::__construct();
	}
	
	private function _displayDraftWarning($active)
	{
		return 
		'<div class="warn draft" style="'.($active ? 'display:none' : '').'">
			<p>
			<span style="float: left">
			<img src="../img/admin/warn2.png" />
			'.$this->l('Your Look page will be saved as a draft').'
			</span>
			<input type="button" class="button" style="float: right;" value="'.$this->l('Save and preview').'" onclick="submitAddlookAndPreview();">
			<input type="hidden" name="previewSubmitAddlookAndPreview" id="previewSubmitAddlookAndPreview" />
			<br class="clear" />
			</p>
		</div>';
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$divLangName = 'meta_title¤meta_description¤meta_keywords¤ccontent¤link_rewrite';
		$all_products = LookObject::getAllProducts((int)($cookie->id_lang), 0, 'ALL', 'id_product', 'ASC', $obj->id, (isset($_POST['look_productBox']) ? Tools::getValue('look_productBox') : false));
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.Tools::getAdminTokenLite('AdminLookbookContent').'" method="post" name="look" id="look" enctype="multipart/form-data">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			'.$this->_displayDraftWarning($obj->active).'
			<fieldset><legend>'.$this->l('Look page').'</legend>';
			
		// META TITLE
		echo '<label>'.$this->l('Look Category:').' </label>
				<div class="margin-form">
					<select name="id_lookbook">';
		$categories = LookbookObject::getCategories((int)($cookie->id_lang), false);
		LookbookObject::recurseLookbook($categories, $categories[0][1], 1, $this->getFieldValue($obj, 'id_lookbook'));
		echo '
					</select>
				</div>
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
		
		// DESCRIPTION
		echo '	<label>'.$this->l('Description:').' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages as $language)
			echo '
					<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'" rows="5" cols="40">'.htmlentities($this->getFieldValue($obj, 'description', (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
		echo '	<p class="clear"></p>
				</div>
				<div class="clear"><br /></div>';
		// Products
		echo '<fieldset style="font-size: 1em">';
		echo '<legend>'.$this->l('Look products').'</legend>';
		echo '	<table>
							<tbody>
								<tr></tr>
								<tr>
									<td>
										<table id="look_product" class="table tableDnD" cellpadding="0" cellspacing="0">
											<thead>
												<tr class="nodrag nodrop">
													<th>
														<input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'look_productBox[]\', this.checked)">
													</th>
													<th>'.$this->l('ID') .'</th>
													<th>'.$this->l('Picture').'</th>
													<th width="540">'.$this->l('Name').'</th>
													<th>'.$this->l('Price').'</th>
													<th>'.$this->l('Quantity').'</th>
													<th>'.$this->l('Status').'</th>
												</tr>
											</thead>
											<tbody>';
											foreach($all_products AS $k => $product)
											{
												// Image
												$image = Db::getInstance()->getRow('
													SELECT id_image
													FROM '._DB_PREFIX_.'image
													WHERE id_product = '.(int)($product['id_product']).' AND cover = 1'
												);
												if (isset($image['id_image']))
												{
													$target = _PS_TMP_IMG_DIR_.'product_mini_'.(int)($product['id_product']).(isset($product['product_attribute_id']) ? '_'.(int)($product['product_attribute_id']) : '').'.jpg';
													if (file_exists($target))
														$products[$k]['image_size'] = getimagesize($target);
													$imageObj = new Image($image['id_image']);
												}
												
												echo '<tr'.((isset($image['id_image']) AND isset($products[$k]['image_size'])) ? ' height="'.($products[$k]['image_size'][1] + 7).'"' : '').'>';
												echo '	<td class="center">';
												if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
												echo '		<input type="checkbox" name="look_productBox[]" class="look_productBox" value="'.$product['id_product'].'" class="noborder" '. (isset($product['look_checked']) && $product['look_checked']	 ? 'checked="checked"' : '' ) .' />';
												echo '</td>';
												echo '	<td>' . $product['id_product'] . '</td>';
												echo '<td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.$imageObj->getExistingImgPath().'.jpg', 'product_mini_look_'.(int)($product['id_product']).(isset($product['id_product_attribute']) ? '_'.(int)($product['id_product_attribute']) : '').'.jpg', 80, 'jpg') : '--').'</td>';
												echo '<td>'.$product['name'].'</td>';
												echo '<td>' . Tools::displayPrice($product['price'], $currency, false) . '</td>';
												echo '<td>'.$product['quantity'].'</td>';
												echo '<td><img src="../img/admin/'.($product['active'] ? 'enabled.gif' : 'disabled.gif').'"</td>';
												echo '</tr>';
											}
		echo '						</tbody>
										</table>
									<td>
								</tr>
							</tbody>
						</table>';
		echo '</fieldset>';

		// Images
		echo '<fieldset style="font-size: 1em; margin-top: 15px" id="fieldset_image">';
		echo '	<legend>'.$this->l('Look images').'</legend>';
		echo '<a href="#" class="add_look_image"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new image').'</a>';

		if(sizeof($obj->getImages()))
		{
			$nbImages = count($obj->getImages());
			foreach($obj->getImages() AS $i => $image)
			{
				echo '	<fieldset style="font-size: 1em; margin-top: 15px" ' . ($i + 1 == $nbImages ? 'class="fieldset_image"' : '') .'>';
				echo '	<p style="float:right"><label>'. $this->l('Delete') .'</label><input type="checkbox" name="delete[]" value="'. $image['image'] .'" /></p>';
				echo '	<legend>Image <span>'.($i + 1).'</span></legend>';
				echo '	<p><label>'. $this->l('Image') .'</label>';
				echo '	<input type="file" name="look_imageBox[]" /></p>';
				echo '	<p><label>'. $this->l('Thumbs') .'</label>';
				echo '	<input type="file" name="look_thumbsBox[]" /></p>';
				echo '	<p><label>'. $this->l('Cover') .'</label>';
				echo '	<input type="radio" name="cover" class="cover" value="'.$i.'" '.($image['cover'] ? 'checked="checked"' : '').' /> <input type="file" name="look_coverBox[]" /></p>';
				echo '	<p style="text-align: center" class="image_ctn"><img src="../modules/lookbook/img/thumbs/'. $image['image'] .'" height="100" /><img src="../modules/lookbook/img/slides/'. $image['image'] .'" height="100" />'. ($image['image'] ? '<img src="../modules/lookbook/img/covers/'. $image['image'] .'" height="100" />' : '') .'</p>';
				echo '	</fieldset>';
			}
		}
		else
		{
			echo '	<fieldset style="font-size: 1em; margin-top: 15px" class="fieldset_image">';
			echo '	<legend>Image <span>1</span></legend>';
			echo '	<p><label>'. $this->l('Image') .'</label>';
			echo '	<input type="file" name="look_imageBox[]" /></p>';
			echo '	<p><label>'. $this->l('Thumbs') .'</label>';
			echo '	<input type="file" name="look_thumbsBox[]" /></p>';
			echo '	<p><label>'. $this->l('Cover') .'</label>';
			echo '	<input type="radio" name="cover" class="cover" value="1" /> <input type="file" name="look_coverBox[]" /></p>';
			echo '	</fieldset>';
		}
		echo '</fieldset>';
		// SUBMIT
		echo '	<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset><br />
			'.$this->_displayDraftWarning($obj->active).'
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

			$(document).ready(function() {
				$(".add_look_image").click(function(e){
					e.preventDefault();
					var newImage = $(".fieldset_image").clone();
					var nbFieldset = $("#fieldset_image fieldset").length + 1;
					newImage.removeClass("fieldset_image");
					newImage.children(".image_ctn").remove();
					//console.log(newImage.children("p").children(".cover"));
					newImage.children("p").children(".cover").attr("value", nbFieldset - 1);
					newImage.children("legend").children("span").html(nbFieldset);
					$("#fieldset_image").append(newImage);
				});
			});
			</script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>';
	}
	
	public function display($token = NULL)
	{
		global $currentIndex, $cookie;
		
		if (($id_lookbook = (int)Tools::getValue('id_lookbook')))
			$currentIndex .= '&id_lookbook='.$id_lookbook;
		$this->getList((int)($cookie->id_lang), !$cookie->__get($this->table.'Orderby') ? 'position' : NULL, !$cookie->__get($this->table.'Orderway') ? 'ASC' : NULL);
		//$this->getList((int)($cookie->id_lang));
		if (!$id_lookbook)
			$id_lookbook = 1;
		echo '<h3>'.(!$this->_listTotal ? ($this->l('No looks found')) : ($this->_listTotal.' '.($this->_listTotal > 1 ? $this->l('looks') : $this->l('look')))).' '.
		$this->l('in lookbook').' "'.stripslashes(LookbookObject::hideLookbookPosition($this->_category->getName())).'"</h3>';
		echo '<a href="'.$currentIndex.'&id_lookbook='.$id_lookbook.'&add'.$this->table.'&token='.Tools::getAdminTokenLite('AdminLookbookContent').'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add a new look').'</a>
		<div style="margin:10px;">';
		$this->displayList($token);
		echo '</div>';
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

	function postProcess()
	{
		global $cookie, $currentIndex;
		
		if (Tools::isSubmit('viewlook') AND ($id_look = (int)(Tools::getValue('id_look'))) AND $look = new LookObject($id_look, (int)($cookie->id_lang)) AND Validate::isLoadedObject($look))
		{
			$redir = $this->getLookLink($look);
			if (!$look->active)
			{
				$admin_dir = dirname($_SERVER['PHP_SELF']);
				$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
				$redir .= '?adtoken='.Tools::encrypt('PreviewLook'.$look->id).'&ad='.$admin_dir;
			}
			Tools::redirectAdmin($redir);
		}
		elseif (Tools::isSubmit('deletelook'))
		{
			$look = new LookObject((int)(Tools::getValue('id_look')));
			$look->cleanPositions($look->id_lookbook);
			if (!$look->delete())
				$this->_errors[] = Tools::displayError('An error occurred while deleting object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
			else
				Tools::redirectAdmin($currentIndex.'&id_lookbook='.$look->id_lookbook.'&conf=1&token='.Tools::getAdminTokenLite('AdminLookbookContent'));
		}/* Delete multiple objects */
		elseif (Tools::getValue('submitDel'.$this->table))
		{
			if ($this->tabAccess['delete'] === '1')
			{
				if (isset($_POST[$this->table.'Box']))
				{
					$look = new LookObject();
					$result = true;
					$result = $look->deleteSelection(Tools::getValue($this->table.'Box'));
					if ($result)
					{
						$look->cleanPositions((int)(Tools::getValue('id_lookbook')));
						Tools::redirectAdmin($currentIndex.'&conf=2&token='.Tools::getAdminTokenLite('AdminLookbookContent').'&id_category='.(int)(Tools::getValue('id_lookbook')));
					}
					$this->_errors[] = Tools::displayError('An error occurred while deleting selection.');

				}
				else
					$this->_errors[] = Tools::displayError('You must select at least one element to delete.');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		elseif (Tools::isSubmit('submitAddlook') OR Tools::isSubmit('submitAddlookAndPreview'))
		{
			parent::validateRules();

			if (!sizeof($this->_errors))
			{
				if (!$id_look = (int)(Tools::getValue('id_look')))
				{
					$look = new LookObject();
					$this->copyFromPost($look, 'look');
					if (!$look->add())
						$this->_errors[] = Tools::displayError('An error occurred while creating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					elseif (Tools::isSubmit('submitAddlookAndPreview'))
					{
						$look->setProducts(Tools::getValue('look_productBox'));
						$look->setImages($_FILES, $_POST['cover']);
						$preview_url = $this->getLookLink($look, $this->getFieldValue($object, 'link_rewrite', $this->_defaultFormLanguage), (int)($cookie->id_lang));
						if (!$look->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewLook'.$look->id);
	
							$preview_url .= $object->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
					{
						$look->setProducts(Tools::getValue('look_productBox'));
						$look->setImages($_FILES, $_POST['cover']);
						Tools::redirectAdmin($currentIndex.'&id_lookbook='.$look->id_lookbook.'&conf=3&token='.Tools::getAdminTokenLite('AdminLookbookContent'));
					}
				}
				else
				{
					$look = new LookObject($id_look);
					$this->copyFromPost($look, 'look');
					if (!$look->update())
						$this->_errors[] = Tools::displayError('An error occurred while updating object.').' <b>'.$this->table.' ('.mysql_error().')</b>';
					elseif (Tools::isSubmit('submitAddlookAndPreview'))
					{
						$look->setProducts(Tools::getValue('look_productBox'));
						if(Tools::getValue('delete'))
						{
							foreach(Tools::getValue('delete') AS $delete)
							{
								$this->deleteImage($id_look, $delete);
							}
						}
						$look->setImages($_FILES, $_POST['cover']);
						$preview_url = $this->getLookLink($look, $this->getFieldValue($object, 'link_rewrite', $this->_defaultFormLanguage), (int)($cookie->id_lang));
						if (!$look->active)
						{
							$admin_dir = dirname($_SERVER['PHP_SELF']);
							$admin_dir = substr($admin_dir, strrpos($admin_dir,'/') + 1);
							$token = Tools::encrypt('PreviewLook'.$look->id);
	
							$preview_url .= $object->active ? '' : '&adtoken='.$token.'&ad='.$admin_dir;
						}
						Tools::redirectAdmin($preview_url);
					}
					else
					{
						$look->setProducts(Tools::getValue('look_productBox'));
						if(Tools::getValue('delete'))
						{
							foreach(Tools::getValue('delete') AS $delete)
							{
								$this->deleteImage($id_look, $delete);
							}
						}
						$look->setImages($_FILES, $_POST['cover']);
						Tools::redirectAdmin($currentIndex.'&id_lookbook='.$look->id_lookbook.'&conf=4&token='.Tools::getAdminTokenLite('AdminLookbookContent'));
					}
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
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_lookbook'))) ? ('&id_lookbook='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminLookbookContent'));
		}
		/* Change object statuts (active, inactive) */
		elseif (Tools::isSubmit('status') AND Tools::isSubmit($this->identifier))
		{
			if ($this->tabAccess['edit'] === '1')
			{
				if (Validate::isLoadedObject($object = $this->loadObject()))
				{
					if ($object->toggleStatus())
						Tools::redirectAdmin($currentIndex.'&conf=5'.((int)Tools::getValue('id_lookbook') ? '&id_lookbook='.(int)Tools::getValue('id_lookbook') : '').'&token='.Tools::getValue('token'));
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

	public function getLookLink($look, $alias = null, $ssl = false, $id_lang = NULL)
	{
		global $link;

		$base = (($ssl AND Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));
	
		if (is_object($look))
		{
			return ((int)Configuration::get('PS_REWRITING_SETTINGS') == 1) ? 
				($base.__PS_BASE_URI__.$link->getLangLink((int)($id_lang)).'content/'.(int)($look->id).'-'.$look->link_rewrite) :
				($base.__PS_BASE_URI__.'look.php?id_look='.(int)($look->id));
		}
		
		if ($alias)
			return ((int)Configuration::get('PS_REWRITING_SETTINGS') == 1) ? ($base.__PS_BASE_URI__.$link->getLangLink((int)($id_lang)).'content/'.(int)($link).'-'.$alias) :
			($base.__PS_BASE_URI__.'link.php?id_look='.(int)($link));
		return $base.__PS_BASE_URI__.'link.php?id_look='.(int)($link);
	}

	public function deleteImage($id, $image)
	{
		Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "look_image` WHERE `id_look` = " . $id . " AND `image` = '" . $image . "'");
		$this->_deleteOldImage(_PS_MODULE_DIR_ . 'lookbook/img/slides/' . $image);
		$this->_deleteOldImage(_PS_MODULE_DIR_ . 'lookbook/img/thumbs/' . $image);
	}

	private function _deleteOldImage($fileName)
	{
	  if(file_exists($fileName))
	    unlink($fileName);
	}
}

