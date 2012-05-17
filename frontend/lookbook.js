$(function() {
	//lookbook
	$('ul.other_lookbook_view li a').click(function(event){
		event.preventDefault();
		var name_picture = $(this).children().attr('src');
		var	name_big_picture = name_picture.replace('thumbs','slides');
		$('.look_big_one').attr('src', name_big_picture);
	});
});