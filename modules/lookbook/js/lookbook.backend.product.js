Prestashop.lookbook.Product = {};

Prestashop.lookbook.Product.toggleProduct = function (element, type, callback) {
  var idProduct = Prestashop.lookbook.Product.getId(element);
  var idCms     = Prestashop.lookbook.Product.getIdCMS();
  console.log(type);
  if(type == 'add')
    type = 'insert_product';
  else
    type = 'delete_product';
    console.log(type)
  $.ajax({
    url: Prestashop.lookbook.ajaxUrl,
    data: {type: type, id_product: idProduct, id_look: idCms},
    success: function(data) {
      var result = {};
      if(data.hasError == false)
        result.success = true;
      else
        result.success = false;

      return callback(result);
    },
    error: function(error){
      console.log('Error: ' + error);
    },
    dataType: Prestashop.lookbook.dataType
  });
}

Prestashop.lookbook.Product.getId = function (element) {
  return element.parent().next().text();
}

Prestashop.lookbook.Product.getIdCMS = function () {
  return $('.id_cms').val();
}