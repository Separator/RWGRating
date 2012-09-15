// получить список пользователя:
function get_players_list() {
	var playersList;
	$.ajax({
		url: 'ajax.php',
		type: 'POST',
		async: false,
		cache: false,
		dataType: 'json',
		data: {action: 'players_list'},
		success: function(data) {playersList = data},
		error: function() {playersList={}}
	});
	if (playersList['error'] == '0')	return playersList['players'];
	else								return {};
};

// заполнить указанный список:
function fill_select(select, playersList) {
	$(select).html('').append('<option value=""><\/option>');
	for (var j in playersList)
		$(select).append('<option value="' + j + '">' + playersList[j] + '<\/option>');
};

$(document).ready(function() {
	playersList = get_players_list();
	fill_select($('select[name=acceptor]'), playersList);
	fill_select($('select[name=donor]'), playersList);
	$('input[name=merge]').click(function() {
		// кажем диалог подтверждения:
		var dialogNode = $('<div>Вы действительно хотите слить этих пользователей?<\/div>').attr({
			title: 'Регистрация'
		}).addClass('dialog').dialog({
			modal: true,
			buttons: {
				'Слить': function() {
					dialogNode.html('<img src="images/ajax-loader.gif" alt="" />');
					dialogNode.dialog({buttons:{}});
					$.ajax({
						url: 'ajax.php',
						type: 'POST',
						async: false,
						cache: false,
						dataType: 'json',
						data: {
							action: 'merge_players',
							acceptor: $('select[name=acceptor]').val(),
							donor: $('select[name=donor]').val()
						},
						success: function(data) {
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
						},
						error: function() {
							dialogNode.html('Неизвестная ошибка выхода!');
							dialogNode.dialog({buttons:{'Отмена': function() {$(this).dialog('close')}}});
						}
					});
				},
				'Отмена': function() {$(this).dialog('close')}
			}
		});
		
		
		
		
	});
});