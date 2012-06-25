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
	$('.map, .check').click(function(event) {
		if (typeof(event.stopPropagation) == 'function') event.stopPropagation();
		if (typeof(event.cancelBubble) == 'boolean') event.cancelBubble = true;
	});
	
	// выделение / отмена выделения карт:
	$('.toggle_maps').click(function() {
		if ($(this).attr('checked'))
			$('.check input').attr('checked', true);
		else
			$('.check input').attr('checked', false);
	});
	// собственно получение выбранных карт:
	$('.get_maps').click(function() {
		// проверка наличия выбранных карт:
		var maps_list = $('.check input:checked');
		if (!maps_list.length) {
			var dialogNode = $('<div>').attr({title: 'Ошибка'}).addClass('dialog').html('Не выбрана ни одна карта!');
			dialogNode.dialog({modal: true,buttons:{'Отмена': function() {$(this).dialog('close')}}});
			return false;
		};
		// формируем форму:
		var formNode = $('<form>').attr({
			'method': 'POST',
			'enctype': 'application/x-www-form-urlencoded',
			'action': 'get_maps.php',
			'target': '_blank'
		});
		// забиваем форму:
		for (var i=0; i < maps_list.length; i++)
			formNode.append('<input type="hidden" name="map[]" value="' + maps_list.eq(i).attr('name') + '" >');
		// собсна посылаем форму:
		formNode.appendTo($('body')).submit();
	});
});