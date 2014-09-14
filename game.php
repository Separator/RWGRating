<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Информация по игре</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/load.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/game.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
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
					$id = get_param('game_id');
					$req_id = db_connect();
					$query  = req_game_by_id($id);
					$result = mysql_query($query, $req_id);
					if ($result):
						$result = get_req_data($result);
						$result = $result[0];
				?>
					<p class="bold">Общие данные:</p>
					
					<label>Имя:
					</label><span><?=$result['GameName'];?></span>
					<br />
					
					<label>Дата проведения:
					</label><span><?=to_local_date($result['GameDate'], $_SESSION['Player']['TimeZone']);?></span>
					<br />
					
					<label>Дата заливки:
					</label><span><?=to_local_date($result['LoadDate'], $_SESSION['Player']['TimeZone']);?></span>
					<br />
					
					<label>Время:</label>
					<span><?=$result['Minutes'];?> мин. <?=$result['Seconds'];?> сек.</span>
					<br />
					
					<label>Карта:
					</label><span><a href="map.php?idmap=<?=$result['IDMap'];?>"><?=$result['MapName'];?></a></span>
					<br />
					
					<label>Мод:
					</label><span><?=$result['ModName'];?></span>
					<br />
					
					<label>Автор:
					</label><span><?=$result['PlayerName'];?></span>
					<br /><br />
					
					<p class="bold">Команды и статистика:</p>
					<?php
						$query  = req_teams_by_game_id($id);
						$result = get_req_data(mysql_query($query, $req_id));
						$result = convert_stats($result);
						foreach ($result as $k => $v) {
							$players = $v['Players'];
					?>
					
					<p>Команда № <?= $k ?>:</p>
					<p>
						Результат:
						<?php
							if ($v['Win'] == '2')	echo("<span style='color:orange;'>ничья</span>");
							elseif ($v['Win'] =='1')echo("<span style='color:green;'>победа</span>");
							else					echo("<span style='color:red;'>поражение</span>");
						?>
					</p>
					
					<? if ($v['ReplayFile']): ?>
					<p><a href="replays/<?= $v['ReplayFile'] ?>">Скачать повтор игры</a></p>
					<? endif ?>
					
					<table class="command">
						<tr class='hdr'>
							<td>Имя игрока</td>
							<td>Пехота<br />уничт/пот</td>
							<td>Танки<br />уничт/пот</td>
							<td>Грузовики<br />уничт/пот</td>
							<td>Самолёты<br />уничт/пот</td>
							<td>ПВО<br />уничт/пот</td>
							<td>Артиллерия<br />уничт/пот</td>
							<td>Корабли-поезда<br />уничт/пот</td>
							<td>Неизвестно<br />уничт/пот</td>
						</tr>
						
					<?php
						foreach ($players as $l => $m) {
					?>
						<tr>
							<td><?= $m['Name'] ?></td>
							<td><?= $m['Infantry'] ?></td>
							<td><?= $m['Tanks'] ?></td>
							<td><?= $m['Trucks'] ?></td>
							<td><?= $m['Aircraft'] ?></td>
							<td><?= $m['AntiAircraft'] ?></td>
							<td><?= $m['Artillery'] ?></td>
							<td><?= $m['TrainsShips'] ?></td>
							<td><?= $m['Unknown'] ?></td>
						</tr>
					<?php
						}
					?>
					</table>
					<br />
					
					<?php
						}
						
						// вытащить рисунок статы:
						$query  = req_get_game_img($id);
						$result = mysql_query($query, $req_id);
						if ($result) {
							$result = get_req_data($result);
                            if (isset($result[0])) {
                                $game_img = $result[0]['Name'];
                            }
					?>
					<h1>
						<img style="width: 600px;" src="stats/images/<?= $game_img ?>" alt="" />
					</h1>
					<?php
						}
						
						// комментарии:
						if ($_SESSION['Player']['ID']) {
					?>
					<p class="bold">Оставить комментарий к игре:</p>
					<form>
						<textarea class="textinput" placeholder="Текст комментария"></textarea>
					</form>
					
					<?php
						}
					?>
				<? else: ?>
					Нет такой игры!
					<a href='index.php'>&lt;&lt; Назад</a>
				<? endif ?>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>