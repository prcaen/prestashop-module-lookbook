<?php
if(!defined('THUMBLIB_BASE_PATH'))
	require_once _PS_MODULE_DIR_ . 'lookbook/backend/classes/libs/phpthumb/ThumbLib.inc.php';

class LookObject extends ObjectModel
{
	public $id;
	public $id_lookbook;
	public $description;

	// SEO
	public $meta_title;
	public $meta_description;
	public $meta_keywords;
	public $link_rewrite;

	public $position;
	public $active;

	public $date_add;
	public $date_upd;

	protected $table = 'look';
	protected $identifier = 'id_look';

	protected $fieldsRequiredLang = array('meta_title', 'link_rewrite');
	protected $fieldsSizeLang = array(
		'meta_description' => 255,
		'meta_keywords' => 255,
		'meta_title' => 128,
		'link_rewrite' => 128,
		'description' => 3999999999999
	);

	protected $fieldsValidate = array('id_lookbook' => 'isUnsignedInt');
	protected $fieldsValidateLang = array(
		'meta_description' => 'isGenericName', 
		'meta_keywords' => 'isGenericName',
		'meta_title' => 'isGenericName',
		'link_rewrite' => 'isLinkRewrite',
		'description' => 'isString'
	);

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_look'] = (int)($this->id);
		$fields['id_lookbook'] = (int)($this->id_lookbook);
		$fields['position'] = (int)($this->position);
		$fields['active'] = (int)($this->active);
		$fields['date_add']	 = pSQL($this->date_add);
		$fields['date_upd']	 = pSQL($this->date_upd);

		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		self::validateFieldsLang();

		return parent::getTranslationsFields(array('meta_title', 'meta_description', 'meta_keywords', 'link_rewrite', 'description'));
	}

	public function __construct($id_look = NULL, $id_lang = NULL, $backoffice = false)
	{
		parent::__construct($id_look, $id_lang);

		$this->products = $this->getProducts($id_lang);
		if($id_look)
		{
			$this->images = $this->getImages();
			$this->cover = $this->getCover();
			$this->nextLook = self::getNextLook($id_look, $id_lang);
			$this->prevLook = self::getPrevLook($id_look, $id_lang);
		}
	}

	public function getImages()
	{
		return Db::getInstance()->ExecuteS('
			SELECT li.`image`, li.`cover`
			FROM `'._DB_PREFIX_.'look_image` li
			WHERE li.`id_look` = ' . (int)$this->id);
	}

	public function getCover()
	{
		return Db::getInstance()->getRow('
			SELECT li.`image`
			FROM `'._DB_PREFIX_.'look_image` li
			WHERE li.`id_look` = '.(int)($this->id).'
			AND li.`cover` = 1');
	}

	public function getProducts($id_lang = NULL)
	{
		$products = array();

		if ($id_lang == NULL)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');

		$results = Db::getInstance()->ExecuteS('
			SELECT lp.`id_product`
			FROM `'._DB_PREFIX_.'look_product` lp
			WHERE lp.`id_look` = '.(int)($this->id));

		if(!empty($results))
		{
			foreach($results AS $result)
				$products[] = new Product($result['id_product'], false, $id_lang);
		}

		return $products;
	}

	public function setImages($files, $cover)
	{
		// Images
		$images = '';
		$nbImages = count($files['look_imageBox']['tmp_name']);
		for($i = 0; $i <= $nbImages; $i++)
		{
			if(isset($files['look_imageBox']['tmp_name'][$i]) AND !empty($files['look_imageBox']['tmp_name'][$i]))
  		{
  			$imgName = $this->id . '_' . $i;
				$ext = $this->_createSlide($files['look_imageBox']['tmp_name'][$i], $files['look_imageBox']['name'][$i], $imgName, true);
				$images .= "('" . $this->id . "', '" . $imgName . $ext . "', '".($cover - 1 == $i ? 1 : 0)."')";
  				if($i + 1 < $nbImages)
						$images .= ', ';
  		}
		}
		if($images != '')
		{
			if(!Db::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "look_image` VALUES " . $images))
				return false;
		}

		// Thumbs
		$nbThumbs = count($files['look_thumbsBox']['tmp_name']);
		for($i = 0; $i <= $nbThumbs; $i++)
		{
			if(isset($files['look_thumbsBox']['tmp_name'][$i]) AND !empty($files['look_thumbsBox']['tmp_name'][$i]))
  		{
  			$imgName = $this->id . '_' . $i;
				$this->_createThumb($files['look_thumbsBox']['tmp_name'][$i], $files['look_thumbsBox']['name'][$i], $imgName, $ext, true);
  		}
		}

		// Cover
		$nbThumbs = count($files['look_coverBox']['tmp_name']);
		for($i = 0; $i <= $nbThumbs; $i++)
		{
			if(isset($files['look_coverBox']['tmp_name'][$i]) AND !empty($files['look_coverBox']['tmp_name'][$i]))
  		{
  			$imgName = $this->id . '_' . $i;
  			$this->_createCover($files['look_coverBox']['tmp_name'][$i], $files['look_coverBox']['name'][$i], $imgName, $ext, true);
  		}
		}
	}

	public function setProducts($look_products)
	{
		if(!is_array($look_products))
			return false;

		if(!Db::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "look_product` WHERE `id_look` = " . $this->id))
			return false;

		$products = '';
		foreach($look_products AS $k => $id_product)
		{
			$products .= "('" . $this->id . "', '" . $id_product . "')";
			if($k + 1 != count($look_products))
				$products .= ', ';
		}

		if(!Db::getInstance()->Execute("INSERT INTO `" . _DB_PREFIX_ . "look_product` VALUES " . $products))
			return false;

		return true;
	}

	public static function cleanPositions($id_lookbook)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_look`
		FROM `'._DB_PREFIX_.'look`
		WHERE `id_lookbook` = '.(int)($id_lookbook).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = '
				UPDATE `'._DB_PREFIX_.'look`
				SET `position` = '.(int)($i).'
				WHERE `id_lookbook` = '.(int)($id_lookbook).'
				AND `id_look` = '.(int)($result[$i]['id_look']);
				Db::getInstance()->Execute($sql);
			}
		return true;
	}

	public static function getAllProducts($id_lang, $start, $limit, $orderBy, $orderWay, $id_look = false, $checked = false, $id_category = false, $only_active = false)
	{
		$all_products = Product::getProducts($id_lang, $start, $limit, $orderBy, $orderWay, $id_category = false, $only_active = false);

		if($checked)
		{
			foreach($all_products AS &$product)
			{
				foreach($checked AS $check)
				{
					if($check == $product['id_product'])
						$product['look_checked'] = 1;
				}
			}
			return $all_products;
		}

		if($id_look)
		{
			$look_products = Db::getInstance()->ExecuteS('SELECT lp.`id_product` FROM `'._DB_PREFIX_.'look_product` lp WHERE lp.`id_look` = ' . (int)$id_look);
			if(!empty($look_products))
			{
				foreach($all_products AS &$product)
				{
					foreach($look_products AS $fproduct)
					{
						if($fproduct['id_product'] == $product['id_product'])
							$product['look_checked'] = 1;
					}
				}
			}
		}

		return $all_products;
	}

	private function _createThumb($tmp_name, $nameFile, $name, $ext, $crop = false)
	{
	  $file   = $tmp_name;
	  $width  = 75;
	  $height = 90;
    $thumb  = PhpThumbFactory::create($file);
    
    $fileName = _PS_MODULE_DIR_ . 'lookbook/img/thumbs/' . $name . $ext;
    
    $this->_deleteOldImage($fileName);
    
    if($crop)
      $thumb->cropFromCenter($width, $height);
    else
      $thumb->resize($width, $height);
      
    $thumb->save($fileName);
	}

	private function _createCover($tmp_name, $nameFile, $name, $ext, $crop = false)
	{
	  $file   = $tmp_name;
	  $width  = 240;
	  $height = 355;
    $thumb  = PhpThumbFactory::create($file);
    
    $fileName = _PS_MODULE_DIR_ . 'lookbook/img/covers/' . $name . $ext;
    
    $this->_deleteOldImage($fileName);
    
    if($crop)
      $thumb->cropFromCenter($width, $height);
    else
      $thumb->resize($width, $height);
      
    $thumb->save($fileName);
	}

	private function _createSlide($tmp_name, $nameFile, $name, $crop = false)
	{
	  $ext    = strrchr($nameFile, '.');
	  $file   = $tmp_name;
	  $width  = 675;
	  $height = 600;
    $thumb  = PhpThumbFactory::create($file);

    $fileName = _PS_MODULE_DIR_ . 'lookbook/img/slides/' . $name . $ext;
    
    $this->_deleteOldImage($fileName);

    if($crop)
      $thumb->cropFromCenter($width, $height);
    else
      $thumb->resize($width, $height);

    $thumb->save($fileName);

    return $ext;
	}

	public static function getNextLook($id_current, $id_lang)
	{
		$sql = 'SELECT l.`id_look`, ll.`link_rewrite`
						FROM `'._DB_PREFIX_.'look` l
						LEFT JOIN `'._DB_PREFIX_.'look_lang` ll ON (l.`id_look` = ll.`id_look`)
						WHERE l.`id_look` > '. (int)$id_current . '
						ORDER BY l.`id_look` ASC
						LIMIT 1';

		return Db::getInstance()->ExecuteS($sql);
	}

	public static function getPrevLook($id_current, $id_lang)
	{
		$sql = 'SELECT l.`id_look`, ll.`link_rewrite`
						FROM `'._DB_PREFIX_.'look` l
						LEFT JOIN `'._DB_PREFIX_.'look_lang` ll ON (l.`id_look` = ll.`id_look`)
						WHERE l.`id_look` < '. (int)$id_current . '
						ORDER BY l.`id_look` ASC
						LIMIT 1';

		return Db::getInstance()->ExecuteS($sql);
	}

	private function _deleteOldImage($fileName)
	{
	  if(file_exists($fileName))
	    unlink($fileName);
	}

	private function _getExtension($file)
	{
	  return strrchr($file['name'], '.');
	}
}
?>