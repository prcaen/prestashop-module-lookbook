if(typeof Prestashop == 'undefined')
  var Prestashop = {};
if(typeof Prestashop.lookbook == 'undefined')
  Prestashop.lookbook = {};

  Prestashop.lookbook.ajaxUrl = 'modules/lookbook/ajax.product.php';
  Prestashop.lookbook.dataType = 'json';

$(document).ready(function() {
  $('.lookbook_link_product').click(onClickLinkProduct);
});

function onClickLinkProduct(e) {
  e.preventDefault();

  var idProduct = $(this).attr('id').split('product_id_')[1];
  
  $.ajax({
    url: Prestashop.lookbook.ajaxUrl,
    data: {id_product: idProduct},
    success: function(data) {
      populateHtml(data);
    },
    error: function(error){
      console.log(error);
    },
    dataType: Prestashop.lookbook.dataType
  });
}

function populateHtml(data) {
  var name = data.name;
  var description_short = data.description_short;
  var price = data.price;
  var description = data.description;
  var images = data.images
  
  console.log(images);
}
