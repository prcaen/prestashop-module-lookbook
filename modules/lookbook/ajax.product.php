<?php
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('../../images.inc.php');

if(isset($_GET['id_product']))
{
  $product = preProcess();
  
  process($product);
}

function preProcess()
{
  global $cookie;

  if($id_product = (int)Tools::getValue('id_product'))
    $product = new Product($id_product, true, $cookie->id_lang);
    
  if (!Validate::isLoadedObject($product))
  {
    die('{"hasError" : true, "errors" : "Product cannot be load"}');
    return false;
  }
  else
    return $product;
}

function process($product)
{
	global $cookie, $cart, $currency;

	if (!Validate::isLoadedObject($product))
		die('{"hasError" : true, "errors" : "Product not found"}');
	else
	{
		if ((!$product->active AND (Tools::getValue('adtoken') != Tools::encrypt('PreviewProduct'.$product->id))))
			die('{"hasError" : true, "errors" : "Product is no longer available."}');
		elseif (!$product->checkAccess((int)$cookie->id_customer))
		  die('{"hasError" : true, "errors" : "You do not have access to this product."}');
		else
		{
		  $datas  = '';
		  $datas .= Tools::jsonEncode($product);
		  // Price
		  $product->price = Tools::displayPrice($product->price, ($product->specificPrice['id_currency'] != 0) ? Currency::getCurrencyInstance($product->specificPrice['id_currency']) : $currency, false);
		  
		  /* Attributes / Groups & colors */
			$colors = array();
			$attributesGroups = $product->getAttributesGroups((int)($cookie->id_lang));
			if (is_array($attributesGroups) AND $attributesGroups)
			{
				$groups = array();
				$combinationImages = $product->getCombinationImages((int)($cookie->id_lang));
				
				// Images
				$images = $product->getImages((int)$cookie->id_lang);
				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if ($image['cover'])
					{
					  $datas .= '"mainImage" :' . Tools::jsonEncode($images[0]);
						$cover = $image;
						$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($product->id.'-'.$image['id_image']) : $image['id_image']);
						$cover['id_image_only'] = (int)($image['id_image']);
					}
					$productImages[(int)$image['id_image']] = $image;
				}
				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById($cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');
				$size = Image::getSize('large');
				$datas .= ',"cover" :' . Tools::jsonEncode($cover);
				$datas .= ',"imgWidth" :' . Tools::jsonEncode((int)($size['width']));
				$datas .= ',"mediumSize" :' . Tools::jsonEncode(Image::getSize('medium'));
				$datas .= ',"largeSize" :' . Tools::jsonEncode(Image::getSize('large'));
				$datas .= ',"accessories" :' . Tools::jsonEncode($product->getAccessories((int)$cookie->id_lang));

				if (count($productImages))
				{
				  $link = new Link();
				  foreach($productImages as &$productImage)
				  {
				    $productImage['large'] = $link->getImageLink($product->link_rewrite, $productImage['id_image'], 'large');
				    $productImage['medium'] = $link->getImageLink($product->link_rewrite, $productImage['id_image'], 'medium');
				  }

				  $datas .= ',"images" :' . Tools::jsonEncode($productImages);
				}

				foreach ($attributesGroups AS $k => $row)
				{
					/* Color management */
					if (((isset($row['attribute_color']) AND $row['attribute_color']) OR (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) AND $row['id_attribute_group'] == $product->id_color_default)
					{
						$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
						$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
						if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
							$colors[$row['id_attribute']]['attributes_quantity'] = 0;
						$colors[$row['id_attribute']]['attributes_quantity'] += (int)($row['quantity']);
					}

					if (!isset($groups[$row['id_attribute_group']]))
					{
						$groups[$row['id_attribute_group']] = array(
							'name' =>			$row['public_group_name'],
							'is_color_group' =>	$row['is_color_group'],
							'default' =>		-1,
						);
					}

					$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
					if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
						$groups[$row['id_attribute_group']]['default'] = (int)($row['id_attribute']);
					if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
						$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)($row['quantity']);

					$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
					$combinations[$row['id_product_attribute']]['attributes'][] = (int)($row['id_attribute']);
					$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
					$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
					$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
					$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
					$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
					$combinations[$row['id_product_attribute']]['ean13'] = $row['ean13'];
					$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
					$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
					$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
				}

				//wash attributes list (if some attributes are unavailables and if allowed to wash it)
				if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
				{
					foreach ($groups AS &$group)
						foreach ($group['attributes_quantity'] AS $key => &$quantity)
							if (!$quantity)
								unset($group['attributes'][$key]);

					foreach ($colors AS $key => $color)
						if (!$color['attributes_quantity'])
							unset($colors[$key]);
				}

				foreach ($groups AS &$group)
					natcasesort($group['attributes']);

				foreach ($combinations AS $id_product_attribute => $comb)
				{
					$attributeList = '';
					foreach ($comb['attributes'] AS $id_attribute)
						$attributeList .= '\''.(int)($id_attribute).'\',';
					$attributeList = rtrim($attributeList, ',');
					$combinations[$id_product_attribute]['list'] = $attributeList;
				}
			}
			$colors = (sizeof($colors) AND $product->id_color_default) ? $colors : false;

			$datas .= ',"groups" :' . Tools::jsonEncode($groups);
			$datas .= ',"combinations" :' . Tools::jsonEncode($combinations);
			$datas .= ',"colors" :' . Tools::jsonEncode($colors);
			$datas .= ',"combinationImages" :' . Tools::jsonEncode($combinationImages);
		  die($datas);
		}
	}
}
?>