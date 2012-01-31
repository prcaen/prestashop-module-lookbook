<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('../../images.inc.php');


if(isset($_GET['type'])) {
  if(Tools::getValue('type') == 'insert_product')
  {
    if(isset($_GET['id_product']) && isset($_GET['id_look']))
    {
      $id_product      = Tools::getValue('id_product');
      $id_look         = Tools::getValue('id_look');

      $values    = "'" . $id_product . "', '" . $id_look . "'";

      $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'lookbook_looks_products` (`id_product`, `id_look`) VALUES (' . $values . ')';

      if(Db::getInstance()->Execute($sql))
        die('{"hasError" : false, "errors" : ""}');
      else
        die('{"hasError" : true, "errors" : ["Insert offer : an error has occured"]}');
    }
    else
      die('{"hasError" : true, "errors" : ["Please provide all data"]} ');
  }
  elseif(Tools::getValue('type') == 'delete_product')
  {
    if(isset($_GET['id_product']) && isset($_GET['id_look']))
    {
      $id_product      = Tools::getValue('id_product');
      $id_look         = Tools::getValue('id_look');

      $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'lookbook_looks_products` WHERE `id_product` = ' . $id_product . ' AND `id_look` = '. $id_look;

      if(Db::getInstance()->Execute($sql))
        die('{"hasError" : false, "errors" : ""}');
      else
        die('{"hasError" : true, "errors" : ["Insert offer : an error has occured"]}');
    }
    else
      die('{"hasError" : true, "errors" : ["Please provide all data"]} ');
  }
}
else
  die('{"hasError" : true, "errors" : ["Please provide all data"]} ');
?>