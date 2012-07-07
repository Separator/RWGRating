<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<?php
			$action = get_param('action');
			if ($action == 'show_edit'):
		?>
		<title>Редактирование карты</title>
		<? else: ?>
		<title>Свойства карты</title>
		<? endif ?>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/map.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/underscore.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<!-- редактирование файла -->
		<script type="text/javascript" src="js/jquery.fileInput.js"></script>
		<link rel="stylesheet" type="text/css" media="all" href="css/jquery.fileInput.css">
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/map.js"></script>
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
					$req_id = db_connect();
					$idmap = get_param('idmap');
					if ($idmap) {
						$query  = req_map_by_id($idmap);
						$result = mysql_query($query, $req_id);
						// сохраняем данные по карте:
						if (mysql_num_rows($result))
							$map_data = get_req_data($result);
					}
					switch ($action) {
						case 'append':	// получаем игровые данные:
										$name        = get_param('name');
										$size        = get_param('size');
										$version     = get_param('version');
										$idmod       = get_param('idmod');
										$description = get_param('description');
										
										if (!$name || !$idmod) {
											echo('<h1>Ошибка добавления. Не указано одно из обязательных полей!</h1>');
											break;
										}
										
										$mapfile = $_FILES['mapfile'];
										if ($mapfile['error'] != '0' || $mapfile['size']=='0') {
											echo('<h1>Ошибка добавления. Не указан файл карты!</h1>');
											break;
										}
										// проверка, является ли файл архивом:
										$filetype = explode("/", $mapfile['type']);
										$filetype = $filetype[1];
										// файл карты должен быть архивом:
										if (strtoupper($filetype) != 'RAR') {
											echo('<h1>Ошибка добавления. Файл не является архивом!</h1>');
											break;
										}
										// проверка наличия файла с таким же именем:
										if (file_exists("maps/".$mapfile['name'])) {
											echo('<h1>Ошибка добавления. Файл карты с заданным именем уже существует!</h1>');
											break;
										}
										
										// записываем данные по карте в базу:
										$query  = req_append_map($name,$size,$version,$idmod,$description,$mapfile['name']);
										$result = mysql_query($query, $req_id);
										if (!$result) {
											echo('Ошибка добавления. Не удалось записать данные в базу!');
											break;
										}
										// вытаскиваем id залитой карты:
										$idmap = mysql_insert_id($req_id);
										// создаём новый файл карты:
										move_uploaded_file($mapfile['tmp_name'], "maps/".$mapfile['name']);
										?>
										<h1>Изменения успешно сохранены!</h1>
										<form method="post">
											<input type="hidden" value="<?= $idmap ?>" name="idmap" />
											<input type="submit" value="Перейти к карте" />
										</form>
										<?php
										break;
					
						case 'f_append': ?>
										<h1>Добавление карты</h1>
										<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST" enctype="multipart/form-data">
											<input type="hidden" name="action" value="append" />
											
											<label>* Название карты: </label>
											<input class="textinput" type="text" name="name" value="" maxlength="50" placeholder="Название карты" />
											<br /><br />
											
											<label>Размер карты: </label>
											<input class="textinput" type="text" name="size" value="" maxlength="10" placeholder="Размер карты" />
											<br /><br />
											
											<label>Версия карты: </label>
											<input class="textinput" type="text" name="version" value="" maxlength="10" placeholder="Версия карты" />
											<br /><br />
											
											<label>* Мод Противостояния:</label>
											<select name="idmod">
												<?php
													$query  = req_mods();
													$result = get_req_data(mysql_query($query, $req_id));
													for ($i=0; $i < count($result); $i++)
														echo("<option value='".$result[$i]['IDMod']."'>".$result[$i]['Name']."</option>\n");
												?>
											</select>
											<br /><br />
											
											<label>* Файл карты (rar-архив):</label>
											<input type="file" name="mapfile" />
											<br /><br />
											
											<label>Описание карты:</label>
											<br />
											<textarea placeholder="Описание карты" class="textinput" cols="100" rows="10" name="description"></textarea>
											<br /><br />
											
											<input type="submit" value="Сохранить" />
										</form>
										<?php
										break;
						
						case 'edit':	if (!$idmap) {
											echo('<h1>Не указан идентификатор карты!</h1>');
											break;
										}
										// получаем обновлённые данные:
										$name        = get_param('name');
										$size        = get_param('size');
										$version     = get_param('version');
										$idmod       = get_param('idmod');
										$description = get_param('description');
										if (!$name || !$idmod) {
											echo('<h1>Ошибка редактирования. Не указано одно из обязательных полей!</h1>');
											break;
										}
										$map_change = get_param('mapchange');
										if ($map_change=='1') {
											$mapfile = $_FILES['mapfile'];
											if ($mapfile['error'] != '0' || $mapfile['size']=='0') {
												echo('<h1>Ошибка редактирования. Не указан файл карты!</h1>');
											break;
											}
											$filename = $mapfile['name'];
											// проверка, является ли файл архивом:
											$filetype = explode("/", $mapfile['type']);
											$filetype = $filetype[1];
											// файл карты должен быть архивом:
											if (strtoupper($filetype) != 'RAR') {
												echo('<h1>Ошибка добавления. Файл не является архивом!</h1>');
												break;
											}
											// удаляем старый файл карты:
											unlink("maps/".$map_data[0]['MapFile']);
											// создаём новый файл карты:
											move_uploaded_file($mapfile['tmp_name'], "maps/".$filename);
											$mapfile = $filename;
										} else
											$mapfile = $map_data[0]['MapFile'];
										$query  = req_update_map($idmap,$name,$size,$version,$idmod,$description,$mapfile);
										$result = mysql_query($query, $req_id);
										if (!$result) {
											echo('<h1>Ошибка редактирования. Не удалось сохранить изменения!</h1>');
											break;
										}
										?>
										<h1>Изменения успешно сохранены!</h1>
										<form method="post">
											<input type="hidden" value="<?= $idmap ?>" name="idmap" />
											<input type="submit" value="Перейти к карте" />
										</form>
										<?php
										break;
						
						case 'f_edit':	if (!$idmap) {
											echo('<h1>Не указан идентификатор карты!</h1>');
											break;
										}
										?>
										<h1>Редактирование карты</h1>
										<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="POST" enctype="multipart/form-data">
											<input type="hidden" name="idmap" value="<?= $map_data[0]['IDMap'] ?>" />
											<input type="hidden" name="action" value="edit" />
											
											<label>* Название карты: </label>
											<input class="textinput" type="text" name="name" value="<?= $map_data[0]['Name'] ?>" maxlength="50" placeholder="Название карты" />
											<br /><br />
											
											<label>Размер карты: </label>
											<input class="textinput" type="text" name="size" value="<?= $map_data[0]['Size'] ?>" maxlength="10" placeholder="Размер карты" />
											<br /><br />
											
											<label>Версия карты: </label>
											<input class="textinput" type="text" name="version" value="<?= $map_data[0]['Version'] ?>" maxlength="10" placeholder="Версия карты" />
											<br /><br />
											
											<label>* Мод Противостояния:</label>
											<select name="idmod">
												<?php
													$query  = req_mods();
													$result = get_req_data(mysql_query($query, $req_id));
													for ($i=0; $i < count($result); $i++)
													if ($result[$i]['IDMod'] == $map_data[0]['IDMod'])
														echo("<option selected='selected' value='".$result[$i]['IDMod']."'>".$result[$i]['Name']."</option>\n");
													else
														echo("<option value='".$result[$i]['IDMod']."'>".$result[$i]['Name']."</option>\n");
												?>
											</select>
											<br />
											
											<div class="map_file"></div>
											
											<label>Описание карты:</label>
											<br />
											<textarea placeholder="Описание карты" class="textinput" cols="100" rows="10" name="description"><?= $map_data[0]['Description'] ?></textarea>
											<br /><br />
											
											<input type="submit" value="Сохранить" />
										</form>
										<?php
										break;
						
						case 'delete':	// проверяем, нет ли игр с заданной картой:
										$query = req_get_games_by_map($idmap);
										$result = mysql_query($query, $req_id);
										if (mysql_num_rows($result)) {
											echo('<h1>Ошибка удаления. Указанная карта учтена в прошедших играх!</h1>');
											break;
										}
										// удаляем заданную карту:
										$query  = req_delete_map($idmap);
										$result = mysql_query($query, $req_id);
										if (!$result) {
											echo('<h1>Ошибка удаления.</h1>');
											break;
										}
										// удаляем файл карты:
										unlink("maps/".$map_data[0]['MapFile']);
										?>
										<h1>Карта успешно удалена!</h1>
										<form method="post" action="maps.php">
											<input type="submit" value="К списку карт" />
										</form>
										<?php
										break;
						
						default:		if (!$idmap) {
											echo('<h1>Не указан идентификатор карты!</h1>');
											break;
										}
										?>
										<h1>Информация по карте</h1>
										<label>Название карты:</label><span><?= $map_data[0]['Name'] ?></span><br />
										<label>Размер:</label><span><?= $map_data[0]['Size'] ?></span><br />
										<label>Версия:</label><span><?= $map_data[0]['Version'] ?></span><br />
										<?php
										$query  = req_mod_by_id($map_data[0]['IDMod']);
										$result = get_req_data(mysql_query($query, $req_id));
										?>
										<label>Мод:</label><span><?= $result[0]['Name'] ?></span><br />
										<label>Файл:</label>
										<span>
											<a href="maps/<?= $map_data[0]['MapFile'] ?>"><?= $map_data[0]['MapFile'] ?></a>
										</span><br /><br />
										<label>Описание:</label>
										<p>
										<?php
										if ($map_data[0]['Description'])
											echo($map_data[0]['Description']);
										else
											echo('Нет описания');
										?>
										</p>
										<?php
										// кнопка редактирования:
										if ($_SESSION['Player']['ID']) {
											$user_settings = get_user_settings($_SESSION['Player']['ID']);
											if (!isset($user_settings['Types'][3])) break;
										?>
										
										<button class="edit_map" idmap="<?= $idmap ?>">Редактировать карту</button>
										<button class="delete_map" idmap="<?= $idmap ?>">Удалить карту</button>
										<?php
										}
										break;
					}
				?>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>