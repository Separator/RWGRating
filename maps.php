<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Список карт</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/maps.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/maps.js"></script>
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
				<h1>Список игровых карт</h1>
				
				<p>
				<?php
					if ($_SESSION['Player']['ID']) {
						$user_settings = get_user_settings($_SESSION['Player']['ID']);
						// если у пользователя есть права на работу с картами:
						if (isset($user_settings['Types'][3])) {
				?>
				
					<button class="add_map">Добавить новую карту</button>
				
				<?php
						}
					}
				?>
					<button class="get_maps" title="Выберите карты из списка и нажмите кнопку">Получить карты</button>
				</p>
				<?php
					// запрашиваем список карт:
					$req_id = db_connect();
					$query  = req_maps();
					$result = mysql_query($query, $req_id);
					if (mysql_num_rows($result)) {
				?>
				<table class="maps_list">
					<tr class="hdr">
						<td>№</td>
						<td>Название</td>
						<td>Размер</td>
						<td>Версия</td>
						<td>Файл</td>
						<td><input type="checkbox" class="toggle_maps" /></td>
					</tr>
				<?php
						$maps = get_req_data($result);
						for ($i=0; $i < count($maps); $i++) {
							$map = $maps[$i];
				?>
					<tr class="map_item" idmap="<?= $map['IDMap'] ?>">
						<td><?= ($i+1) ?></td>
						<td class="to_left"><?= $map['Name'] ?></td>
						<td><?= $map['Size'] ?></td>
						<td><?= $map['Version'] ?></td>
						<td class="map"><a href="maps/<?= $map['MapFile'] ?>"><?= $map['MapFile'] ?></a></td>
						<td class="check"><input type="checkbox" name="<?= $map['MapFile'] ?>" /></td>
					</tr>
				<?php
						}
				?>
				</table>
				<?php
					} else {
						echo('<p>На данный момент здесь нет ни одной карты<\/p>');
					}
				?>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>