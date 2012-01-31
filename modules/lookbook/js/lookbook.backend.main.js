if(typeof Prestashop == 'undefined')
  var Prestashop = {};
  Prestashop.lookbook = {};
  Prestashop.lookbook.ajaxUrl = '../modules/lookbook/ajax.php';
  Prestashop.lookbook.dataType = 'json';

$(document).ready(function() {
  $(".selected_lookbook_product").click(onClickLookbookProduct);
});

function onClickLookbookProduct(e) {
  e.preventDefault();
  var element = $(this);
  var type    = 'add';
  var img     = element.children(img);
  if(img.attr('title') == 'Enabled')
    type = 'delete';
  else
    type = 'add';
    console.log(type);
  Prestashop.lookbook.Product.toggleProduct(element, type, function(cb) {
    if(cb.success) {
      console.log('success');
      if(type == 'delete')
        img.attr('alt', 'Disabled').attr('title', 'Disabled').attr('src', '../img/admin/disabled.gif');
      else
        img.attr('alt', 'Enabled').attr('title', 'Enabled').attr('src', '../img/admin/enabled.gif');
    }
    else {
      alert('Error');
    }
  });
}