function get_maps_list(idmod) {
	var result;
	$.ajax({
		async: false,
		type: 'POST',
		url: 'ajax.php',
		data: {
			idmod: idmod,
			action: 'get_maps_list'
		},
		dataType: 'json',
		success: function(data) {result = data},
		error: function() {result = {maps:{}}}
	});
	return result['maps'];
};

function update_maps_list(mapsList) {
	var mapsSelect = $('select[name=game_map]');
	// удаляем из списка старые карты:
	$('option', mapsSelect).remove();
	for (var j in mapsList) {
		mapsSelect.append('<option value="'+j+'">' + mapsList[j] + '<\/option>');
	};
};

$(document).ready(function() {
	// создаем календарь для выбора даты проведения игры:
	var gameDate = $('input[name=game_date]');
	gameDate.datepicker({dateFormat: 'yy/mm/dd'});
	// устанавливаем дату по умолчанию:
	gameDate.datepicker('setDate', '0d');
	// создаем элемент для выбора времени окончания игры:
	var gameTime = $('input[name=game_time]');
	if (gameTime.length) {
		gameTime.timePicker({
			show24Hours: true,
			separator:':'
		});
		$.timePicker(gameTime).setTime(new Date(new Date()));
	};
	$('select[name=game_mode]').change(function() {
		var mapsList   = get_maps_list($(this).val());
		update_maps_list(mapsList);
	});
	// автоматом рисуем список карт:
	var mapsList   = get_maps_list($('select[name=game_mode]').val());
	update_maps_list(mapsList);
});