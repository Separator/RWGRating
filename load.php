<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Загрузка игры</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/load.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<!-- выбор времени -->
		<script type="text/javascript" src="js/jquery.timePicker.js"></script>
		<link rel="stylesheet" type="text/css" media="all" href="css/jquery.timePicker.css">
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/load.js"></script>
	</head>
	
	<body>
		<div class="header b_radius">
			<div class="log b_radius">
				<?php require_once 'templates/login.tpl'; ?>
			</div>
		</div>
		
		<div class="content clearfix b_radius">
			<div class="nav_panel b_radius">
				<ul>
					<?php require_once 'templates/navigation.tpl'; ?>
				</ul>
				
				<ul>
					<?php require_once 'templates/forums.tpl'; ?>
				</ul>
			</div>
			
			<div class="content_panel b_radius">
				<?php
					$back = " <a href='{$_SERVER[SCRIPT_NAME]}'>&lt;&lt; Назад</a>";
					
					$step = get_param('step');
					switch($step):
						case '1':	$game_name    = get_param('game_name');
									$game_date    = get_param('game_date', "date");
									$game_time    = get_param('game_time');
									$game_comment = get_param('game_comment');
									$game_map     = get_param('game_map');
									$game_mode    = get_param('game_mode');
									$image_file   = $_FILES['image_file'];
									$stat_file    = $_FILES['stat_file'];
									
									// проверки:
									if (!$game_name) {
										echo("Вы не указали название игры!".$back);
										break;
									}
									if (!$game_date) {
										echo("Вы не указали дату проведения игры!".$back);
										break;
									}
									if (!$game_time) {
										echo("Вы не указали время окончания игры!".$back);
										break;
									}
									if (!$game_map) {
										echo("Вы не указали карту!".$back);
										break;
									}
									if (!$game_mode) {
										echo("Вы не указали мод!".$back);
										break;
									}
									if (!$stat_file || $stat_file['error']!='0') {
										echo("Вы не указали файл статистики!".$back);
										break;
									}
									if ($stat_file['size']>=10000) {
										echo("Слишком большой файл статистики!".$back);
										break;
									}
									if ($stat_file['type']!='text/xml') {
										echo("Неверный формат файла статистики!".$back);
										break;
									}
									if ($image_file['tmp_name'] && $image_file['error'] != '0') {
										echo("Ошибка передачи файла изображения статистики!".$back);
										break;
									}
									
									// разбор файла статы:
									$_SESSION['Game'] = array(
										'step'         => $step,
										'game_name'    => $game_name,
										'game_date'    => local_datetime_to_timestamp(
															$game_date,
															$game_time,
															$_SESSION['Player']['TimeZone']),
										'game_time'    => $game_time,
										'game_comment' => $game_comment,
										'game_map'     => $game_map,
										'game_mode'    => $game_mode,
										'stat_file'    => xml_file_to_arr($stat_file['tmp_name']),
										'image_file'   => $image_file
									);
									
									if (!$_SESSION['Game']['stat_file']) {
										echo("Неверный формат файла статистики статистики!".$back);
										break;
									}
									// если игра с текущим хэшем уже есть:
									$req_id = db_connect();
									$query  = req_game_exist($_SESSION['Game']['stat_file']['MD5']);
									$result = get_req_data(mysql_query($query, $req_id));
									if (count($result)) {
										echo("Данная игра уже учтена. Спасибо!".$back);
										break;
									}
									// сохраняем xml-файл:
									$ftype = explode("/", $stat_file['type']);
									$ftype = $ftype[1];
									$fname = "{$_SESSION['Game']['stat_file']['MD5']}.$ftype";
									$fdir  = getcwd()."\\stats\\xml";
									$fullname = "$fdir\\$fname";
									move_uploaded_file($stat_file['tmp_name'], "$fdir\\$fname");
									
				?>
				
				<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST" enctype="multipart/form-data">
					<p class="bold">Заливка игры (шаг 2):</p>
					
					<label>* Номер победившей команды: </label>
					<select name="win_team">
						<?php
							$teams = $_SESSION['Game']['stat_file']['Teams'];
							for ($i=0; $i < count($teams); $i++) {
								$index = $teams[$i]['Index'];
								echo("<option value='$index'>$index команда</option>\n");
							}
						?>
						<option value="5">Ничья</option>
					</select>
					<br /><br />
					
					<label>Файлы с повтором игры: </label><br />
					<?php
						for ($i=0; $i < count($teams); $i++) {
							$index = $teams[$i]['Index'];
					?>
							<label>Повтор команды № <?= $index ?> (rar-архив):</label>
							<input type="file" name="rep[<?= $index ?>]" />
							<br />
					<?php
						}
					?>
					
					<br />
					<input type="hidden" name="step" value="2" />
					<label>Название игры: </label><span><?=$game_name;?></span>
					<br />
					
					<label>Дата проведения игры:</label><span><?=$game_date;?></span>
					<br />
					
					<label>Время окончания игры:</label><span><?=$game_time;?></span>
					<br />
					
					<?php
						$query  = req_mod_by_id($_SESSION['Game']['game_mode']);
						$result = get_req_data(mysql_query($query, $req_id));
						$mod_name = $result[0]['Name'];
					?>
					<label>Мод:</label><span><?=$mod_name;?></span>
					<br />
					
					<?php
						$query  = req_map_by_id($_SESSION['Game']['game_map']);
						$result = get_req_data(mysql_query($query, $req_id));
						$map_name = $result[0]['Name'];
					?>
					<label>Карта:</label><span><?=$map_name;?></span>
					<br />
					
					<?php
						$g_min = $_SESSION['Game']['stat_file']['Time']['Minutes'];
						$g_sec = $_SESSION['Game']['stat_file']['Time']['Seconds'];
					?>
					<label>Время игры:</label><span><?=$g_min;?> мин. <?=$g_sec;?> сек.</span>
					<br /><br />
					
					<label>Команды и статистика:</label><br />
					<?php
						for ($i=0; $i < count($teams); $i++) {
							echo("<label>Команда № {$teams[$i]['Index']}:</label>\n");
							echo("<table class='command'>\n");
							echo("<tr class='hdr'><td>Имя игрока</td>");
							echo("<td>Пехота<br />уничт/пот</td>");
							echo("<td>Танки<br />уничт/пот</td>");
							echo("<td>Грузовики<br />уничт/пот</td>");
							echo("<td>Самолёты<br />уничт/пот</td>");
							echo("<td>ПВО<br />уничт/пот</td>");
							echo("<td>Артиллерия<br />уничт/пот</td>");
							echo("<td>Корабли-поезда<br />уничт/пот</td>");
							echo("<td>Неизвестно<br />уничт/пот</td></tr>");
							
							for ($j=0; $j < count($teams[$i]['Players']); $j++) {
								$player = $teams[$i]['Players'][$j];
								echo("<tr><td class='left'>{$player['Name']}</td>");
								echo("<td>{$player['InfantryKills']}/");
								echo("{$player['InfantryLoses']}</td>");
								echo("<td>{$player['TanksKills']}/");
								echo("{$player['TanksLoses']}</td>");
								echo("<td>{$player['TrucksKills']}/");
								echo("{$player['TrucksLoses']}</td>");
								echo("<td>{$player['AircraftKills']}/");
								echo("{$player['AircraftLoses']}</td>");
								echo("<td>{$player['AntiAircraftKills']}/");
								echo("{$player['AntiAircraftLoses']}</td>");
								echo("<td>{$player['ArtilleryKills']}/");
								echo("{$player['ArtilleryLoses']}</td>");
								echo("<td>{$player['TrainsShipsKills']}/");
								echo("{$player['TrainsShipsLoses']}</td>");
								echo("<td>{$player['UnknownKills']}/");
								echo("{$player['UnknownLoses']}</td></tr>");
							}
							echo("</table>");
						}
					?>
					
					<? if ($image_file['tmp_name']): ?>
					<label>Изображение статистики:</label>
					<br />
					<?php
						$ftype = explode("/", $image_file['type']);
						$ftype = $ftype[1];
						$fname = "{$_SESSION['Game']['stat_file']['MD5']}.$ftype";
						$_SESSION['Game']['Image'] = $fname;
						$fdir  = getcwd()."\\stats\\images";
						$fullname = "$fdir\\$fname";
						move_uploaded_file($image_file['tmp_name'], "$fdir\\$fname");
					?>
					<img class="stat_img" src="stats/images/<?= $fname ?>" alt="" />
					<br />
					<br />
					<?
						else:
							$_SESSION['Game']['Image'] = '';
						endif
					?>
					
					<? if ($game_comment): ?>
					<label>Комментарий к игре:</label>
					<p class="comment"><?=$game_comment;?></p>
					<? endif ?>
					<input type="button" value="Отмена" onclick="location.href='<?= $_SERVER['SCRIPT_NAME'] ?>'" />
					<input name="upload" type="submit" value="Далее" />
				</form>
				
				<?php
									break;
				
						case '2':	$win_team = get_param('win_team');
									if (!$win_team) {
										echo("Победившая команда не была указана!".$back);
										break;
									}
									$teams = $_SESSION['Game']['stat_file']['Teams'];
									// для каждой команды вытаскиваем результат:
									for ($i=0; $i < count($teams); $i++) {
										$team    = $teams[$i];
										$index   = $team['Index'];
										if ($win_team == 5)
											$_SESSION['Game']['stat_file']['Teams'][$i]['Win'] = 2;
										else {
											if ($index == $win_team)
												$_SESSION['Game']['stat_file']['Teams'][$i]['Win'] = 1;
											else
												$_SESSION['Game']['stat_file']['Teams'][$i]['Win'] = 0;
										}
									}
									// для каждой команды вытаскиваем повтор игры:
									$rep = $_FILES['rep'];
									if ($rep) {
										for ($i=0; $i < count($teams); $i++) {
											$team    = $teams[$i];
											$index   = $team['Index'];
											if ($rep['error'][$index]=='0' && $rep['tmp_name'][$index]) {
												$ftype = explode("/", $rep['type'][$index]);
												$ftype = $ftype[1];
												if (strtoupper($ftype) == "RAR") {
													$fname = "$index-{$_SESSION['Game']['stat_file']['MD5']}.$ftype";
													$fdir  = getcwd()."\\replays";
													$fullname = "$fdir\\$fname";
													move_uploaded_file($rep['tmp_name'][$index], "$fdir\\$fname");
													$_SESSION['Game']['stat_file']['Teams'][$i]['Rep'] = $fname;
												} else {
													$_SESSION['Game']['stat_file']['Teams'][$i]['Rep'] = '';
												}
											} else
												$_SESSION['Game']['stat_file']['Teams'][$i]['Rep'] = '';
										}
									}
									// если игра с текущим хэшем уже есть:
									$req_id = db_connect();
									$query  = req_game_exist($_SESSION['Game']['stat_file']['MD5']);
									$result = get_req_data(mysql_query($query, $req_id));
									if (count($result)) {
										echo("Данная игра была учтена ранее. Спасибо!".$back);
										break;
									} else {
										
									}
									// записываем игру:
									$query  = req_create_game();
									$result = mysql_query($query, $req_id);
									$id = mysql_insert_id($req_id);
									
									// делаем запись о рисунке статы, если он был:
									if ($_SESSION['Game']['Image']) {
										$query  = req_create_game_img($id, $_SESSION['Game']['Image']);
										mysql_query($query, $req_id);
									}
									
									// Добавляем коммент к игре:
									if ($_SESSION['Game']['game_comment']) {
										$query  = req_create_comment($id, $_SESSION['Player']['ID'], $_SESSION['Game']['game_comment']);
										mysql_query($query, $req_id);
									}
									
									// записываем игравшие команды и стату игроков:
									$teams = $_SESSION['Game']['stat_file']['Teams'];
									for ($i=0; $i < count($teams); $i++) {
										$team    = $teams[$i];
										$index   = $team['Index'];
										$players = $team['Players'];
										$rep     = $team['Rep'];
										$win     = $team['Win'];
										$query   = req_create_team($win, $id, $index, $rep);
										mysql_query($query, $req_id);
										$tid = mysql_insert_id($req_id);
										// ну а теперь стату по игрокам:
										for ($j=0; $j < count($players); $j++) {
											$player = $players[$j];
											$login  = $player['Name'];
											// смотрим, учтен ли игрок, и если нет,
											// то учитываем его:
											$query  = req_player_by_name($login);
											$result = mysql_query($query, $req_id);
											if (mysql_num_rows($result)) {
												// пользователь уже есть, просто берём ID:
												$result = get_req_data($result);
												$playerid = $result[0]['IDPlayer'];
											} else {
												// учитываем пользователя и берём ID:
												$query    = req_calculate_user_1($login);
												$result   = mysql_query($query, $req_id);
												$playerid = mysql_insert_id($req_id);
												$query    = req_reg_unrecorded_player_2($playerid);
												$result   = mysql_query($query, $req_id);
											}
											// теперь собственно записываем стату по игрокам:
											$query  = req_create_player_stat($playerid, $tid, $player);
											$result = mysql_query($query, $req_id);
										}
									}
									
				?>
				
				<form action="game.php" method="POST" enctype="multipart/form-data">
					<p>Игра успешно залита!</p>
					<input type="hidden" name="game_id" value="<?= $id ?>" />
					<input name="to_game" type="submit" value="К залитой игре" />
				</form>
				
				<?php
									break;
						default:	$_SESSION['Game'] = array();
				?>
				<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST" enctype="multipart/form-data">
					<p class="bold">Заливка игры (шаг 1):</p>
					<input type="hidden" name="step" value="1" />
					<label>* Название игры:</label>
					<input placeholder="Название игры" class="textinput" type="text" name="game_name" value="" maxlength="100" />
					<br /><br />
					
					<label>* дата проведения игры:</label>
					<input class="textinput" type="text" name="game_date" value="" maxlength="50" />
					<br /><br />
					
					<label>* время окончания игры:</label>
					<input class="textinput" type="text" name="game_time" value="" maxlength="50" />
					<br /><br />
					
					<label>Комментарий к игре:</label>
					<br />
					<textarea placeholder="Текст комментария" class="textinput" cols="50" rows="10" name="game_comment"></textarea>
					<br /><br />
					
					<label>Изображение статистики:</label>
					<input type="file" name="image_file" />
					<br /><br />
					
					<label>* Статистика:</label>
					<input type="file" name="stat_file" />
					<br /><br />
					
					<label>* Карта:</label>
					<select name="game_map">
						<?php
							$req_id = db_connect();
							$query  = req_maps();
							$result = get_req_data(mysql_query($query, $req_id));
							for ($i=0; $i < count($result); $i++)
								echo("<option value='".$result[$i]['IDMap']."'>".$result[$i]['Name']."</option>\n");
						?>
					</select>
					<br /><br />
					
					<label>* Мод Противостояния:</label>
					<select name="game_mode">
						<?php
							$query  = req_mods();
							$result = get_req_data(mysql_query($query, $req_id));
							for ($i=0; $i < count($result); $i++)
								echo("<option value='".$result[$i]['IDMod']."'>".$result[$i]['Name']."</option>\n");
						?>
					</select>
					<br />
					<br />
					<input name="upload" type="submit" value="Далее" />
				</form>
				<? endswitch ?>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>