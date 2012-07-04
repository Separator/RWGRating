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
});