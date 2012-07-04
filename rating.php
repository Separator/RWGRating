<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Рейтинг</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/maps.css">
		
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
				<h1>Рейтинг игроков</h1>
			
				<?php
					// создаём объект для работы с БД:
					$base = new RWGDBaseWork($base_settings['host'], $base_settings['base'], $base_settings['user'], $base_settings['password']);
					// получаем тип рейтинга:
					$rating_type = ($_POST['rating_type']) ? $_POST['rating_type'] : '1';
					// создаём объект рейтинга:
					switch ($rating_type) {
						case '1':	$rating = new DualDeploymentRating($base, $rating_type); break;
						default:	$rating = new DualDeploymentRating($base, $rating_type); break;
					}
					// формируем таблицу рейтинга:
					$rating_data = $rating->get_players_rating();
				?>
				<table class="maps_list">
					<tr class="hdr">
						<td>№</td>
						<td>Игрок</td>
						<td>Рейтинг</td>
					</tr>
				<?php
					// выводим таблицу рейтинга:
					$i = 0;
					foreach ($rating_data as $pKey => $player) {
						$i++;
				?>
					<tr>
						<td><?= $i ?></td>
						<td><a href="player.php?playerid="<?= $player['IDPlayer'] ?>"><?= $player['Name'] ?></a></td>
						<td><?= $player['Value'] ?></td>
					</tr>
				<?php
					}
				?>
				</table>
				
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>