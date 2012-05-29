<?php
class LookbookObject extends ObjectModel
{
	public $id;
	public $id_parent;
	public $name;
	public $description;
	public $level_depth;

	// SEO
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $link_rewrite;

	public $position;
	public $active = 1;

	public $date_add;
	public $date_upd;

	protected static $_links = array();

	protected $table = 'lookbook';
	protected $tables = array('lookbook', 'lookbook_lang');
	protected $identifier = 'id_lookbook';

	protected $fieldsRequired = array('id_parent', 'active');
 	protected $fieldsSize = array('id_parent' => 10, 'active' => 1);
	protected $fieldsValidate = array('active' => 'isBool', 'id_parent' => 'isUnsignedInt');
	protected $fieldsRequiredLang = array('name', 'link_rewrite');
 	protected $fieldsSizeLang = array('name' => 64,
 		'link_rewrite' => 64,
 		'meta_title' => 128,
 		'meta_description' => 255,
 		'meta_keywords' => 255);
 	protected $fieldsValidateLang = array(
 		'name' => 'isCatalogName',
 		'link_rewrite' => 'isLinkRewrite',
 		'description' => 'isCleanHtml',
		'meta_title' => 'isGenericName',
		'meta_description' => 'isGenericName',
		'meta_keywords' => 'isGenericName');

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_lookbook'] = (int)($this->id);
		$fields['id_parent'] = (int)($this->id_parent);
		$fields['level_depth'] = (int)($this->level_depth);
		$fields['active'] = (int)($this->active);
		$fields['position'] = (int)($this->position);
		$fields['date_add']	 = pSQL($this->date_add);
		$fields['date_upd']	 = pSQL($this->date_upd);

		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		self::validateFieldsLang();

		return parent::getTranslationsFields(array('name', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'link_rewrite'));
	}

	public function __construct($id_lookbook = NULL, $id_lang = NULL, $subLookbooks = false)
	{
		parent::__construct($id_lookbook, $id_lang);

		$this->covers = $this->getCovers();
		$this->looks = $this->getLooks($id_lang);
		if($subLookbooks)
			$this->lookbooks = $this->getSubLookbooks($id_lang);
	}

	public function getCovers()
	{
		return Db::getInstance()->getRow('
			SELECT li.`image`
			FROM `'._DB_PREFIX_.'look_image` li
			LEFT JOIN `'._DB_PREFIX_.'look` l ON (l.`id_look` = li.`id_look` AND l.`id_lookbook` = '.(int)($this->id).')
			WHERE li.`cover` = 1');
	}

	public static function hideLookbookPosition($name)
	{
		return preg_replace('/^[0-9]+\./', '', $name);
	}

	public function getName($id_lang = NULL)
	{
		if (!$id_lang)
		{
			global $cookie;

			if (isset($this->name[$cookie->id_lang]))
				$id_lang = $cookie->id_lang;
			else
				$id_lang = (int)(Configuration::get('PS_LANG_DEFAULT'));
		}
		return isset($this->name[$id_lang]) ? $this->name[$id_lang] : '';
	}

	public function getLooks($id_lang = NULL)
	{
		$looks = array();

		if($id_lang == NULL)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');

		$results = Db::getInstance()->ExecuteS('
			SELECT l.`id_look`
			FROM `'._DB_PREFIX_.'look` l
			WHERE l.`id_lookbook` = '.(int)($this->id) .'
			AND l.`active` = 1');

		foreach($results AS $result)
			$looks[] = new LookObject($result['id_look'], $id_lang);

		return $looks;
	}

	public static function cleanPositions($id_lookbook_parent)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_lookbook`
		FROM `'._DB_PREFIX_.'lookbook`
		WHERE `id_parent` = '.(int)($id_lookbook_parent).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = '
				UPDATE `'._DB_PREFIX_.'lookbook`
				SET `position` = '.(int)($i).'
				WHERE `id_parent` = '.(int)($id_lookbook_parent).'
				AND `id_lookbook` = '.(int)($result[$i]['id_lookbook']);
				Db::getInstance()->Execute($sql);
			}
		return true;
	}

	public function getSubLookbooks($id_lang = NULL)
	{
		$lookbooks = array();

		if($id_lang == NULL)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');

		$results = Db::getInstance()->ExecuteS('
			SELECT l.`id_lookbook`
			FROM `'._DB_PREFIX_.'lookbook` l
			WHERE l.`id_lookbook` = '.(int)($this->id) .'
			AND l.`active` = 1');

		foreach($results AS $result)
			$lookbooks[] = new LookbookObject($result['id_lookbook'], $id_lang);

		return $lookbooks;
	}

	public static function getCategories($id_lang, $active = true, $order = true)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'lookbook` l
		LEFT JOIN `'._DB_PREFIX_.'lookbook_lang` ll ON ll.`id_lookbook` = l.`id_lookbook`
		WHERE `id_lang` = '.(int)($id_lang).'
		'.($active ? 'AND `active` = 1' : '').'
		ORDER BY `name` ASC');

		if (!$order)
			return $result;

		$categories = array();
		foreach ($result AS $row)
			$categories[$row['id_parent']][$row['id_lookbook']]['infos'] = $row;
		return $categories;
	}

	public static function recurseLookbook($categories, $current, $id_lookbook = 1, $id_selected = 1, $is_html = 0)
	{
		$html = '<option value="'.$id_lookbook.'"'.(($id_selected == $id_lookbook) ? ' selected="selected"' : '').'>'.
		str_repeat('&nbsp;', $current['infos']['level_depth'] * 5).self::hideLookbookPosition(stripslashes($current['infos']['name'])).'</option>';
		if ($is_html == 0)
			echo $html;
		if (isset($categories[$id_lookbook]))
			foreach (array_keys($categories[$id_lookbook]) AS $key)
				$html .= self::recurseLookbook($categories, $categories[$id_lookbook][$key], $key, $id_selected, $is_html);
		return $html;
	}

	public static function checkBeforeMove($id_lookbook, $id_parent)
	{
		if ($id_lookbook == $id_parent) return false;
		if ($id_parent == 1) return true;
		$i = (int)($id_parent);

		while (42)
		{
			$result = Db::getInstance()->getRow('SELECT `id_parent` FROM `'._DB_PREFIX_.'lookbook` WHERE `id_lookbook` = '.(int)($i));
			if (!isset($result['id_parent'])) return false;
			if ($result['id_parent'] == $id_lookbook) return false;
			if ($result['id_parent'] == 1) return true;
			$i = $result['id_parent'];
		}
	}
}
?>