function register_dialog() {
	var dialogNode     = $('<div>').attr({title: 'Регистрация'}).addClass('dialog');
	// получаем список незареганных, но учтенных пользователей:
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		async: false,
		cache: false,
		dataType: 'json',
		data: {action: 'get_not_reg'},
		success: function(data) {
			if (data && data['error'] == '0' && data['users'].length) {
				dialogNode.append('<p style="text-align:left;color:red;">Внимание! Перед тем, как ввести Ваше имя, проверьте список учтённых, но не зарегистрированных пользователей!<\/p>');
				// создаём список:
				var select = $('<select><option><\/option><\/select>').addClass('user_select');
				for (var i=0; i < data['users'].length; i++) {
					select.append('<option>' + data['users'][i]['Name'] + '<\/option>');
				};
				select.click(function() {
					if ($(this).val())
						$('input[name=login]').val($(this).val());
				});
				dialogNode.append('<br>', select, '<br><br>');
			};
		},
		error: function() {}
	});
	var labelOneNode   = $('<label>Логин:<\/label>');
	var labelTwoNode   = $('<label>Пароль:<\/label>');
	var labelThreeNode = $('<label>Капча:<\/label>');
	var labelFourNode  = $('<label>Подтвердить пароль:<\/label>');
	var labelFiveNode  = $('<label>Временная зона:<\/label>');
	var inputOneNode   = $('<input type="text" name="login" \/>');
	var inputTwoNode   = $('<input type="password" name="password" \/>');
	var inputThreeNode = $('<input type="text" name="captcha" \/>');
	var inputFourNode  = $('<input type="password" name="confirm_password" \/>');
	var captchaNode    = $('<img src="images/ajax-loader.gif" alt="" \/>');
	var timeZoneNode   = $('<div class="timezone">');
	dialogNode
		.append(labelOneNode).append('<br>')
		.append(inputOneNode).append('<br>')
		.append(labelTwoNode).append('<br>')
		.append(inputTwoNode).append('<br>')
		.append(labelFourNode).append('<br>')
		.append(inputFourNode).append('<br>')
		
		.append(labelFiveNode).append('<br>')
		.append(timeZoneNode)
		
		.append(labelThreeNode).append('<br>')
		.append(inputThreeNode).append('<br>')
		.append(captchaNode);
	// рисуем список временных зон:
	timeZoneNode.timezoneOffset();
		
	// запрос на получение капчи:
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		data: {action:'captcha'},
		success: function(captcha) {
			captchaNode.attr('src', captcha);
			dialogNode.dialog({
				width: 500,
				buttons: {
					'Отмена': function() {$(this).dialog('close')},
					'Далее': function() {
						var that = this;
						var data = {
							action: 'register',
							login: $('input[name=login]', that).val(),
							password: $('input[name=password]', that).val(),
							confirm_password: $('input[name=confirm_password]', that).val(),
							captcha: $('input[name=captcha]', that).val(),
							timezone: $('.timezone_select').val()
						};
						// сохраняем в куках значения логина и пароля:
						$.cookie('user_login'   , data.login   , {expires:365});
						$.cookie('user_password', data.password, {expires:365});
						// рисуем "загрузчик":
						dialogNode.html('<img src="images/ajax-loader.gif" alt="" />');
						dialogNode.dialog({buttons:{}});
						// посылаем запрос на авторизацию:
						$.ajax({
							url: 'ajax.php',
							type: 'POST',
							data: data,
							success: function(data) {
								try {
									data = eval('(' + data + ')');
									dialogNode.html(data.message);
									switch (data.error) {
										case '0':	dialogNode.dialog({
														buttons:{'Ок': function() {location.href=location.href}},
														close: function() {location.href=location.href}
													});
													break;
										
										default:	dialogNode.dialog({
														buttons:{'Отмена': function() {$(this).dialog('close')}},
														close: function() {}
													});
													break;
									};
								} catch (e) {
									dialogNode.html('Неизвестная ошибка регистрации!');
									dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
								};
							},
							error: function(e) {
								dialogNode.html('Неизвестная ошибка регистрации!');
								dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
							}
						});
					}
				}
			});
		},
		error: function() {
			dialogNode.html('Не удалось получить капчу!');
		}
	});
	
	dialogNode.dialog({
		modal: true,
		buttons: {
			'Отмена': function() {$(this).dialog('close')}
		}
	});
};

function login_dialog() {
	var dialogNode = $('<div>').attr({title: 'Авторизация'}).addClass('dialog');
	var labelOneNode = $('<label>Логин:<\/label>');
	var labelTwoNode = $('<label>Пароль:<\/label>');
	var inputOneNode = $('<input type="text" name="login" \/>');
	var inputTwoNode = $('<input type="password" name="password" \/>');
	// если логин и пароль есть в куках:
	if ($.cookie('user_login'   )) inputOneNode.val($.cookie('user_login'   ));
	if ($.cookie('user_password')) inputTwoNode.val($.cookie('user_password'));
	dialogNode
		.append(labelOneNode).append('<br>')
		.append(inputOneNode).append('<br>')
		.append(labelTwoNode).append('<br>')
		.append(inputTwoNode);
	
	dialogNode.dialog({
		modal: true,
		buttons: {
			'Отмена': function() {$(this).dialog('close')},
			'Далее': function() {
				var that = this;
				var data = {
					action: 'login',
					login: $('input[name=login]', that).val(),
					password: $('input[name=password]', that).val()
				};
				// сохраняем в куках значения логина и пароля:
				$.cookie('user_login'   , data.login   , {expires:365});
				$.cookie('user_password', data.password, {expires:365});
				// рисуем "загрузчик":
				dialogNode.html('<img src="images/ajax-loader.gif" alt="" />');
				dialogNode.dialog({buttons:{}});
				// посылаем запрос на авторизацию:
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: data,
					success: function(data) {
						try {
							data = eval('(' + data + ')');
							dialogNode.html(data.message);
							switch (data.error) {
								case '0':	dialogNode.dialog({
												buttons:{'Ок': function() {location.href=location.href}},
												close: function() {location.href=location.href}
											});
											break;
								
								default:	dialogNode.dialog({
												buttons:{'Отмена': function() {$(this).dialog('close')}},
												close: function() {}
											});
											break;
							};
						} catch (e) {
							dialogNode.html('Неизвестная ошибка авторизации!');
							dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
						};
					},
					error: function(e) {
						dialogNode.html('Неизвестная ошибка авторизации!');
						dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
					}
				});
			}
		}
	});
};

function logout_dialog() {
	var dialogNode = $('<div>').attr({title: 'Выход'}).addClass('dialog');
	dialogNode.append('<label>Вы действительно хотите выйти?<\/label>');
	
	dialogNode.dialog({
		modal: true,
		buttons: {
			'Отмена': function() {$(this).dialog('close')},
			'Далее': function() {
				var that = this;
				// рисуем "загрузчик":
				dialogNode.html('<img src="images/ajax-loader.gif" alt="" />');
				dialogNode.dialog({buttons:{}});
				// посылаем запрос на авторизацию:
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {action: 'logout'},
					success: function(data) {
						try {
							data = eval('(' + data + ')');
							dialogNode.html(data.message);
							switch (data.error) {
								case '0':	dialogNode.dialog({
												buttons:{'Ок': function() {location.href=location.href}},
												close: function() {location.href=location.href}
											});
											break;
								
								default:	dialogNode.dialog({
												buttons:{'Отмена': function() {$(this).dialog('close')}},
												close: function() {}
											});
											break;
							};
						} catch (e) {
							dialogNode.html('Неизвестная ошибка выхода!');
							dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
						};
					},
					error: function(e) {
						dialogNode.html('Неизвестная ошибка выхода!');
						dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
					}
				});
			}
		}
	});
};

$(document).ready(function() {
	// убираем ебучий РАДУКС:
	$('body > *').each(function() {if (this.nodeName != 'DIV') $(this).remove()});
	// прикручиваем авторизацию, выход, регистрацию:
	$('.login').click(login_dialog);
	$('.logout').click(logout_dialog);
	$('.register').click(register_dialog);
});