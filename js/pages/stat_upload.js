$(document).ready(function() {
	// создаем календарь для выбора даты проведения игры:
	var gameDate = $('input[name=game_date]');
	gameDate.datepicker({dateFormat: 'dd/mm/yy'});
	// устанавливаем дату по умолчанию:
	gameDate.datepicker('setDate', '0d');
});