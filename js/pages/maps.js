$(document).ready(function() {
	// кнопка добавления карты:
	$('.add_map').click(function() {
		var formNode = $('<form>').attr({
			'method': 'POST',
			'enctype': 'application/x-www-form-urlencoded',
			'action': 'map.php'
		});
		formNode.append(
			$('<input type="hidden" name="action" value="f_append" >')
		).appendTo($('body')).submit();
	});
	// выбор карты:
	$('.map_item').click(function() {
		var idmap = $(this).attr('idmap');
		var formNode = $('<form>').attr({
			'method': 'POST',
			'enctype': 'application/x-www-form-urlencoded',
			'action': 'map.php'
		}).append(
			$('<input type="hidden" name="idmap" value="' + idmap + '" >')
		).appendTo($('body')).submit();
	});
	// отменить всплытие события при попытке скачать карту:
	$('.map').click(function(event) {
		if (typeof(event.stopPropagation) == 'function') event.stopPropagation();
		if (typeof(event.cancelBubble) == 'boolean') event.cancelBubble = true;
	});
	
});