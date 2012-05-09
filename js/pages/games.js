$(document).ready(function() {
	// реакция на выбор игры из списка:
	$('.game_item').click(function() {
		var gameID = $(this).attr('id');
		$('#game_form').append('<input type="hidden" name="game_id" value="'+gameID+'" />').submit();
	});
	// убираем выбор страницы в случае, если основные параметры поиска изменены:
	$('select').change(function() {
		$('#pager').hide();
		$('.pager_position, .pager_segment').remove();
	});
});