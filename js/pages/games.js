$(document).ready(function() {
	// реакция на выбор игры из списка:
	$('.game_item').click(function() {
		var gameID = $(this).attr('id');
		$('#game_form').append('<input type="hidden" name="game_id" value="'+gameID+'" />').submit();
	});
	// убираем выбор страницы в случае, если основные параметры поиска изменены:
	$('select').change(function() {
		$('#pager, #pager2').hide();
		$('.pager_position, .pager_segment').remove();
	});
	// отменить всплытие события при попытке перейти в игру:
	$('.delete_td').click(function(event) {
		if (typeof(event.stopPropagation) == 'function') event.stopPropagation();
		if (typeof(event.cancelBubble) == 'boolean') event.cancelBubble = true;
	});
	// открытие диалогового окна удаления игры:
	$('.delete_game').click(function() {
		// получаем id игры:
		var gameId = $(this).attr('id').split('_')[1];
		// выводим диалоговое окно:
		var dialogNode = $('<div>').attr({title: 'Подтверждение удаления'}).addClass('dialog').html('Вы действительно хотите удалить эту игру?');
		
		
		dialogNode.dialog({
			modal: true,
			buttons: {
				'Отмена': function() {$(this).dialog('close')},
				'Удалить': function() {
					var data = {
						action: 'delete_game',
						id: gameId
					};
					// рисуем "загрузчик":
					dialogNode.html('<img src="images/ajax-loader.gif" alt="" />');
					dialogNode.dialog({buttons:{}});
					// посылаем запрос на удаление игры:
						$.ajax({
							url: 'ajax.php',
							type: 'POST',
							data: data,
							dataType: 'json',
							success: function(data) {
								try {
									dialogNode.html(data.message);
									switch (data.error) {
										case '0':	dialogNode.dialog({
														buttons:{'Ок': function() {location.href=location.href}},
														close: function() {location.href=location.href}
													});
													break;
										
										default:	dialogNode.dialog({
														buttons:{'Отмена': function() {$(this).dialog('close')}},
														close: function() {location.href=location.href}
													});
													break;
									};
								} catch (e) {
									dialogNode.html('Неизвестная ошибка удаления игры!');
									dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
								};
							},
							error: function(e) {
								dialogNode.html('Неизвестная ошибка удаления игры!');
								dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
							}
						});
				}
			}
		});
		
	
		
	});
	
	
});