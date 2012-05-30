$(document).ready(function() {
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
	
});