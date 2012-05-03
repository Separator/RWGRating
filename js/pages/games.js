$(document).ready(function() {
	$('.game_item').click(function() {
		var gameID = $(this).attr('id');
		$('#game_form').append('<input type="hidden" name="game_id" value="'+gameID+'" />').submit();
	});
});