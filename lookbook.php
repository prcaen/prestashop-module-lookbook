<?php
if (!defined('_PS_VERSION_'))
	exit;

//include_once _PS_MODULE_DIR_ . 'module/backend/classes/Module.php';	 // EDIT

class Lookbook extends Module
{
	public static $moduleName = 'lookbook';

	public function __construct()
	{
		$this->name		 = 'lookbook';
		$this->tab		 = 'front_office_features';
		$this->version = '1.0';
		$this->author	 = 'Pierrick CAEN';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Lookbook');
		$this->description = $this->l('Create looks compose of your products');

		$this->_tplFile				 = _PS_MODULE_DIR_ . $this->name . '/backend/tpl/' . $this->name . '.backend.configure.tpl';
		$this->_adminClassName = 'AdminLookbookContent';
		$this->_idTabParent		= Tab::getIdFromClassName('AdminCatalog');
		$this->_adminTabName	 = array(
			1 => 'Lookbook',
			2 => 'Lookbook',
			3 => 'Lookbook',
			4 => 'Lookbook',
			5 => 'Lookbook',
		);

		$this->_abbreviation = 'LKBK';
		$this->_debugView = true;
		$this->_configs = array(
			1 => array(
				'config_name'		=> $this->_abbreviation . '_A_CONFIG',
				'name'			=> strtolower($this->name) . '_a_config',
				'title'		=> $this->l('A config'),
				'type'		=> 'boolean', // boolean, text, radio, select, checkbox or false
				'validate' => 'isUnsignedInt',
				'default' => 1,
				'help'		=> $this->l('A sentence for help') // optional
			)
		); // EDIT

		$this->_hooks = array(
			1 => array(
				'name'	=> 'header',
				'insert' => false
			)
		);
	}

	/*public function getContent()
	{
		$output	 = '';
		$output .= $this->_postProcess();

		return $output.$this->displayForm();
	}*/

	public function displayForm()
	{
		global $smarty;

		foreach($this->_configs as $key => &$config)
		{
			if(!$config['type'])
				unset($this->_configs[$key]);
			else
				$config['value'] = Configuration::get($config['config_name']);
		}

		$smarty->assign('action', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$smarty->assign('display_name', $this->displayName);
		$smarty->assign('module_name', strtolower($this->name));
		$smarty->assign('module_dir', $this->_path);
		$smarty->assign('configs', $this->_configs);

		$cache_id = $compile_id = ($this->_debugView ? Tools::passwdGen(8) : null);
		return $smarty->fetch($this->_tplFile, $cache_id, $compile_id);
	}

	private function _postProcess()
	{
		$output = '';

		if(Tools::isSubmit('submit_' . strtolower($this->name)))
		{
			foreach($this->_configs as $config)
			{
				if($config['type'])
				{
					if($config['type'] == 'image')
					{
						// Upload image
						if (isset($_FILES[$config['name']]) AND isset($_FILES[$config['name']]['tmp_name']) AND !empty($_FILES[$config['name']]['tmp_name']))
						{
							if ($error = checkImage($_FILES[$config['name']], Tools::convertBytes(ini_get('upload_max_filesize'))))
								$errors .= $error;
							else
							{
								if($name = $this->_createPicture($_FILES[$config['name']], $this->_imgPath))
								{
									if(!Configuration::updateValue($config['config_name'], $name))
										return false;
								}
							}
						}
					}
					else
					{
						if(!Configuration::updateValue($config['config_name'], Tools::getValue($config['name'])))
							return false;
					}
				}
			}
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}

		return $output;
	}

	// ---------------------------
	// --------- HOOKS -----------
	// ---------------------------
	public function hookHeader($params)
	{
		global $smarty, $cookie;
		
		$vars = array(
			'path'		=> $this->_path,
			'id_lang' => (int)$cookie->id_lang,
			'logged'	=> isset($cookie->id_customer) && $cookie->isLogged() ? true : false,
		);

		Tools::addCSS($this->_path . $this->name . '.css', 'all');
		Tools::addJS($this->_path	 . $this->name . '.js');

		foreach($this->_hooks as $hook)
		{
			if($hook['insert'])
				$smarty->assign('HOOK_' . strtoupper($this->name) . '_' . strtoupper($hook['name']), Module::hookExec($hook['name']));
		}

		$smarty->assign('module_'. strtolower($this->name) . '_header' , $vars);
	}

	// ---------------------------
	// --- INSTALL / UNINSTALL ---
	// ---------------------------
	public function install()
	{
		parent::install();

		if(!$this->_installTables() || !$this->_installHooks())
			return false;

		if(!$this->_installModuleTab($this->_adminClassName, $this->_adminTabName, $this->_idTabParent))
			return false;

		foreach($this->_hooks as $hook)
		{
			if(!$this->registerHook($hook['name']))
				return false;
		}

		foreach($this->_configs as $config)
		{
			if(!Configuration::updateValue($config['config_name'], $config['default']))
				return false;
		}

		@copy(_PS_MODULE_DIR_ . $this->name . '/logo.gif', _PS_IMG_DIR_ . 't/' . $this->_adminClassName . '.gif');

		return true;
	}

	public function uninstall()
	{
		parent::uninstall();
		
		if(!$this->_uninstallTables() || !$this->_uninstallHooks())
			return false;

		if(!$this->_uninstallModuleTab($this->_adminClassName))
			return false;

		foreach($this->_configs as $config)
		{
			if(!Configuration::deleteByName($config['config_name']))
				return false;
		}

		@unlink(_PS_IMG_DIR_ . 't/' . $this->_adminClassName . '.gif');

		return true;
	}

	private function _installTables()
	{
		$database	 = Db::getInstance();
		$charset	 = 'utf8';
		$engine		 = (defined('_MYSQL_ENGINE_') ? _MYSQL_ENGINE_ : 'InnoDB');

		// Add lookbook table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . strtolower($this->name) . '` (
			`id_'. strtolower($this->name) .'` int(10) unsigned NOT NULL auto_increment,
			`id_parent` int(10) unsigned NOT NULL,
			`level_depth` tinyint(3) unsigned NOT NULL,
			`position` int(10) unsigned NOT NULL,
			`active` tinyint(1) unsigned NOT NULL,
			`image` varchar(50) NOT NULL,
			`date_add` datetime NOT NULL,
			`date_upd` datetime NOT NULL,
			PRIMARY KEY	 (`id_'. strtolower($this->name) .'`)
		)	 ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add lookbook_lang table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . strtolower($this->name) . '_lang` (
			`id_'. strtolower($this->name) .'` int(10) unsigned NOT NULL,
			`id_lang` tinyint(2) unsigned NOT NULL,
			`name` varchar(128) NOT NULL,
			`description` TEXT,
			`link_rewrite` varchar(128) NOT NULL,
			`meta_title` varchar(128),
			`meta_keywords` varchar(255),
			`meta_description` varchar(255),
			PRIMARY KEY	 (`id_'. strtolower($this->name) .'`,`id_lang`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add look table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'look` (
			`id_look` int(10) unsigned NOT NULL auto_increment,
			`id_lookbook` int(10) unsigned NOT NULL,
			`position` int(10) unsigned NOT NULL,
			`active` tinyint(1) unsigned NOT NULL,
			`date_add` datetime NOT NULL,
			`date_upd` datetime NOT NULL,
			PRIMARY KEY	 (`id_look`)
		)	 ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add look_lang table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'look_lang` (
			`id_look` int(10) unsigned NOT NULL,
			`id_lang` tinyint(2) unsigned NOT NULL,
			`description` TEXT,
			`link_rewrite` varchar(128) NOT NULL,
			`meta_title` varchar(128),
			`meta_keywords` varchar(255),
			`meta_description` varchar(255),
			PRIMARY KEY	 (`id_look`,`id_lang`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add look_product table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'look_product` (
			`id_look` int(10) unsigned NOT NULL,
			`id_product` int(10) unsigned NOT NULL,
			PRIMARY KEY	 (`id_look`,`id_product`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Add look_image table
		$sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'look_image` (
			`id_look` int(10) unsigned NOT NULL,
			`image` varchar(50) NOT NULL,
			`thumbs` varchar(50) NOT NULL,
			`cover` varchar(50),
			PRIMARY KEY	 (`id_look`,`image`)
			) ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset . ';';

		if(!$database->Execute($sql))
			return false;

		// Data insert
		$sql = "INSERT INTO `" . _DB_PREFIX_ . "lookbook` (
			`id_lookbook`,
			`id_parent`,
			`level_depth`,
			`position`,
			`active`,
			`image`,
			`date_add`,
			`date_upd`
			)
			VALUES (
			'1',  '0',  '0',  '0',  '1', NULL ,  '2012-04-16 14:00:00',  '2012-04-16 14:00:00'
			);";

		if(!$database->Execute($sql))
			return false;

		$sql = "INSERT INTO  `" . _DB_PREFIX_ . "lookbook_lang` (
			`id_lookbook` ,
			`id_lang` ,
			`name` ,
			`description` ,
			`link_rewrite` ,
			`meta_title` ,
			`meta_keywords` ,
			`meta_description`
			)
			VALUES (
			'1',  '2',  'Accueil', NULL ,  'accueil', NULL , NULL , NULL
			);";

		if(!$database->Execute($sql))
			return false;

		return true;
	}

	private function _uninstallTables()
	{
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . strtolower($this->name) .'`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . strtolower($this->name) .'_lang`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'look`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'look_lang`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'look_product`'))
			return false;
		if(!Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'look_image`'))
			return false;
		return true;
	}

	private function _installHooks()
	{
		foreach($this->_hooks as $hook)
		{
			if($hook['insert'])
			{
				$sql = "INSERT INTO `" . _DB_PREFIX_ . "hook` SET `name`= '". $hook['name'] ."', `title`= '". $hook['title'] ."', `description`= '". $hook['description'] ."'";
				if(!DB::getInstance()->Execute($sql))
					return false;
			}
		}

		return true;
	}

	private function _uninstallHooks()
	{
		foreach($this->_hooks as $hook)
		{
			if($hook['insert'])
			{
				if(!DB::getInstance()->Execute("DELETE FROM `" . _DB_PREFIX_ . "hook` WHERE `name` = '" . $hook['name'] . "'"))
					return false;
			}
		}

		return true;
	}

	private function _installModuleTab($className, $name, $idParent)
	{
		$tab = new Tab();

		$tab->class_name = $className;
		$tab->name			 = $name;
		$tab->module		 = $this->name;
		$tab->id_parent	 = $idParent;

		if(!$tab->save())
		{
			$this->_errors[] = Tools::displayError('An error occurred while saving new tab: ') . ' <b>' . $tab->name . ' (' . mysql_error() . ')</b>';
			return false;
		}

		$fields = array(
			'id_profile' => 1,
			'id_tab' 		 => (int)$tab->id,
			'view' 			 => 1,
			'add' 			 => 1,
			'edit' 			 => 1,
			'delete' 		 => 1
		);

		Db::getInstance()->autoExecute(_DB_PREFIX_.'access', $fields, 'INSERT');

		return true;
	}

	private function _uninstallModuleTab($className)
	{
		$idTab = Tab::getIdFromClassName($className);

		if($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();

			$fields = array(
				'id_profile' => 1,
				'id_tab' 		 => $idTab,
				'view' 			 => 1,
				'add' 			 => 1,
				'edit' 			 => 1,
				'delete' 		 => 1
			);

			Db::getInstance()->autoExecute(_DB_PREFIX_.'access', $fields, 'DELETE');

			return true;
		}
		else
			return false;
	}

	// ---------------------------
	// --------- TOOLS -----------
	// ---------------------------
	private function _createPicture($file, $path, $action = null, $name = null, $with = null, $height = null)
	{
		$img = PhpThumbFactory::create($file['tmp_name']);

		if(!$name)
			$name = $file['name'];
		else
		{
			$ext = $this->_getFileExtension($file);
			$name .= $ext; 
		}

		if($action)
		{
			switch($action)
			{
				case 'cropFromCenter':
					if(!$width || !$height)
						return false;

					$img->cropFromCenter($width, $height);
					break;
				case 'resize':
					if(!$width || !$height)
						return false;

					$img->resize($width, $height);
					break;
				case 'adaptiveResize':
					if(!$width || !$height)
						return false;

					$img->adaptiveResize($width, $height);
			}
		}
		$fileName = $path . $name;
		
		$img->save($fileName);

		return $name;
	}

	private function _getFileExtension($file)
	{
		return strrchr($file['name'], '.');
	}

	private function _deleteFile($fileName)
	{
		if(file_exists($fileName))
			return unlink($fileName);
		else
			return false;
	}
}
?>