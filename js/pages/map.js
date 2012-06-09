$(document).ready(function() {
	// добавляем поле редактирования файла карты:
	$('.map_file').fileInput({
		phrases: {
			'edit': '* Редактировать карту (rar)'
		}
	});
	// кнопка редактирования карты:
	$('.edit_map').click(function() {
		var formNode = $('<form>').attr({
			'method': 'POST',
			'enctype': 'application/x-www-form-urlencoded',
			'action': 'map.php'
		});
		formNode.append(
			$('<input type="hidden" name="action" value="f_edit" >'),
			$('<input type="hidden" name="idmap" value="' + $(this).attr('idmap') + '" >')
		).appendTo($('body')).submit();
	});
	// кнопка удаления карты:
	$('.delete_map').click(function() {
		var formNode = $('<form>').attr({
			'method': 'POST',
			'enctype': 'application/x-www-form-urlencoded',
			'action': 'map.php'
		});
		formNode.append(
			$('<input type="hidden" name="action" value="delete" >'),
			$('<input type="hidden" name="idmap" value="' + $(this).attr('idmap') + '" >')
		).appendTo($('body')).submit();
	});
});