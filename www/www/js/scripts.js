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

$("body").on("click", "a.ajax", function (event) {
    event.preventDefault();
    $.get(this.href);
});

$("body").on("submit", "form.ajax", function () {
    $(this).ajaxSubmit();
    return false;
});

$("body").on("click", "form.ajax :submit", function () {
    $(this).ajaxSubmit();
    return false;
});