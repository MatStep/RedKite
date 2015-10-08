function unsetAndSetActive(menu, submenu) {
	$('sidebar-menu').children().removeClass('active');

	$(menu).addClass('active');
	$(submenu).addClass('active');
}

$(document).ready(function() {
	var image = $("#img-preview");
	var input = $("#img-input input");

	input.on("change", function(event) {
	image.attr("src", URL.createObjectURL(event.target.files[0]));
	});
});