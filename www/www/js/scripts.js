function unsetAndSetActive(menu, submenu) {
	$('sidebar-menu').children().removeClass('active');

	$(menu).addClass('active');
	$(submenu).addClass('active');
}