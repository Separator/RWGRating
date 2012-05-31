$(document).ready(function() {
	// проверка логина:
	$('.check_login').click(function() {
		// отключаем кнопку:
		$(this).attr('disabled', true);
		$('.loader_wrapper').html('<img alt="" src="images/ajax-loader.gif" />');
		// вытаскиваем логин:
		var login = $('input[name=login]').val();
		// делаем аякс-запрос:
		$.ajax({
			context: this,
			url: 'ajax.php',
			type: 'POST',
			timeout: 5000,
			data: {action: 'check_login', login: login},
			async: true,
			dataType: 'json',
			success: function(data) {
				try {
					// разблокировка кнопки:
					$(this).attr('disabled', false);
					$('.loader_wrapper').html(data['message'] || 'Неизвестная ошибка');
					if (data['error'] == '0')
						$('.loader_wrapper').addClass('check_success').removeClass('check_error');
					else
						$('.loader_wrapper').addClass('check_error').removeClass('check_success');
				} catch (e) {
					$('.loader_wrapper').addClass('check_error').html('Неизвестная ошибка');
				};
			},
			error: function(e) {
				// разблокировка кнопки:
				$(this).attr('disabled', false);
				// выводим сообщение пользователю:
				$('.loader_wrapper').html('Неизвестная ошибка сервера').addClass('check_error');
			}
		});
	});
	// сменить логин:
	$('input[name=change_login]').click(function() {
		// отключаем кнопку:
		$(this).attr('disabled', true);
		// формируем узел диалогового окна:
		var dialogNode = $('<div>').attr({'title':'Смена логина'}).html('<img alt="" src="images/ajax-loader.gif" />');
		dialogNode.dialog({modal: true,closeOnEscape: false});
		$('html body .ui-dialog-titlebar-close').hide();
		// вытаскиваем логин:
		var login = $('input[name=login]').val();
		// делаем аякс-запрос:
		$.ajax({
			context: this,
			url: 'ajax.php',
			type: 'POST',
			timeout: 5000,
			data: {action: 'change_login', login: login},
			async: true,
			dataType: 'json',
			success: function(data) {
				try {
					dialogNode.html(data['message']);
					// разблокировка кнопки:
					$(this).attr('disabled', false);
					if (data['error'] == '0') {
						dialogNode.dialog({
							buttons:{'Ок': function() {location.href=location.href}},
							close: function() {location.href=location.href}
						});
					} else {
						dialogNode.dialog({
							buttons: {'Отмена': function() {$(this).dialog('close')}}
						});
					};
					$('html body .ui-dialog-titlebar-close').show();
				} catch (e) {
					dialogNode.html('Неизвестная ошибка').dialog({
						buttons: {'Отмена': function() {$(this).dialog('close')}}
					});
					$('html body .ui-dialog-titlebar-close').show();
				};
			},
			error: function(e) {
				// разблокировка кнопки:
				$(this).attr('disabled', false);
				// выводим сообщение пользователю:
				dialogNode.html('Неизвестная ошибка').dialog({
					buttons: {'Отмена': function() {$(this).dialog('close')}}
				});
				$('html body .ui-dialog-titlebar-close').show();
			}
		});
	});
	// сменить пароль:
	$('input[name=change_password]').click(function() {
		// отключаем кнопку:
		$(this).attr('disabled', true);
		// формируем узел диалогового окна:
		var dialogNode = $('<div>').attr({'title':'Смена пароля'}).html('<img alt="" src="images/ajax-loader.gif" />');
		dialogNode.dialog({modal: true,closeOnEscape: false});
		$('html body .ui-dialog-titlebar-close').hide();
		// вытаскиваем пароли:
		var password         = $('input[name=password]').val();
		var confirm_password = $('input[name=confirm_password]').val();
		
		
		// делаем аякс-запрос:
		$.ajax({
			context: this,
			url: 'ajax.php',
			type: 'POST',
			timeout: 5000,
			data: {action: 'change_password', password: password, confirm_password: confirm_password},
			async: true,
			dataType: 'json',
			success: function(data) {
				try {
					dialogNode.html(data['message']);
					// разблокировка кнопки:
					$(this).attr('disabled', false);
					if (data['error'] == '0') {
						dialogNode.dialog({
							buttons:{'Ок': function() {$(this).dialog('close')}}
						});
					} else {
						dialogNode.dialog({
							buttons: {'Отмена': function() {$(this).dialog('close')}}
						});
					};
					$('html body .ui-dialog-titlebar-close').show();
				} catch (e) {
					dialogNode.html('Неизвестная ошибка').dialog({
						buttons: {'Отмена': function() {$(this).dialog('close')}}
					});
					$('html body .ui-dialog-titlebar-close').show();
				};
			},
			error: function(e) {
				// разблокировка кнопки:
				$(this).attr('disabled', false);
				// выводим сообщение пользователю:
				dialogNode.html('Неизвестная ошибка').dialog({
					buttons: {'Отмена': function() {$(this).dialog('close')}}
				});
				$('html body .ui-dialog-titlebar-close').show();
			}
		});
	});
});