function openInNewWindow(url) {
	var newWindow = window.open(url, '_blank');
	newWindow.focus();
	return false;
}
function redirect(url) {
	window.location.href = url;
}
