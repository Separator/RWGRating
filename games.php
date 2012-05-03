<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Список игр</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/games.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/games.js"></script>
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
				<form action="games.php" method="POST" id="games_form">
					<?php
						// подключаемся к базе:
						$req_id = db_connect();
					?>
					
					<table class="search_table">
						<tr class="hdr">
							<td>Автор</td>
							<td>Мод</td>
							<td>Карта</td>
							<td>Поиск</td>
						</tr>
						
						<tr>
							<td>
								<select name="restrictions[SP.IDPlayer]">
									<option value="">Все авторы</option>
									<?php
										$query  = get_authors();
										$result = mysql_query($query, $req_id);
										if (mysql_num_rows($result)) {
											$result = get_req_data($result);
											for ($i=0; $i < count($result); $i++)
											if ($_REQUEST['restrictions']['IDPlayer'] == $result[$i]['IDPlayer'])
												echo("<option selected='selected' value='{$result[$i]['IDPlayer']}'>{$result[$i]['Name']}</option>");
											else
												echo("<option value='{$result[$i]['IDPlayer']}'>{$result[$i]['Name']}</option>");
										}
									?>
								</select>
							</td>
							
							<td>
								<select name="restrictions[SMD.IDMod]">
									<option value="">Все модификации</option>
									<?php
										$query  = req_mods();
										$result = mysql_query($query, $req_id);
										if (mysql_num_rows($result)) {
											$result = get_req_data($result);
											for ($i=0; $i < count($result); $i++)
											if ($_REQUEST['restrictions']['IDMod'] == $result[$i]['IDMod'])
												echo("<option selected='selected' value='{$result[$i]['IDMod']}'>{$result[$i]['Name']}</option>");
											else
												echo("<option value='{$result[$i]['IDMod']}'>{$result[$i]['Name']}</option>");
										}
									?>
								</select>
							</td>
							
							<td>
								<select name="restrictions[SM.IDMap]">
									<option value="">Все карты</option>
									<?php
										$query  = req_maps();
										$result = mysql_query($query, $req_id);
										if (mysql_num_rows($result)) {
											$result = get_req_data($result);
											for ($i=0; $i < count($result); $i++)
											if ($_REQUEST['restrictions']['IDMap'] == $result[$i]['IDMap'])
												echo("<option selected='selected' value='{$result[$i]['IDMap']}'>{$result[$i]['Name']}</option>");
											else
												echo("<option value='{$result[$i]['IDMap']}'>{$result[$i]['Name']}</option>");
										}
									?>
								</select>
							</td>
							
							<td>
								<input type="submit" class="search_but" value="Искать" />
							</td>
						</tr>
					</table>
					
					<div class="pager">
						<input type="hidden" name="" />
						<?php
							
						?>
					</div>
				</form>
					
				<form action="game.php" method="POST" id="game_form">
					<?php
						$restrictions = $_REQUEST['restrictions'];
						if ($restrictions['limit']) {
							// ставим лимит в конец:
							$lim = $restrictions['limit'];
							unset($restrictions['limit']);
							$restrictions['limit'] = $lim;
						} else
							$restrictions['limit'] = array(0=>0, 1=>20);
						
						$query  = req_games($restrictions);
						$result = mysql_query($query, $req_id);
						$result = get_req_data($result);
						if (count($result)) {
					?>
					
					<table class="games_list">
						<tr class="hdr">
							<td>№</td>
							<td>Название</td>
							<td>Мод</td>
							<td>Карта</td>
							<td>Дата игры</td>
							<td>Дата заливки</td>
							<td>Минуты</td>
							<td>Секунды</td>
							<td>Автор</td>
						</tr>
						
						<?php
							for ($i=0; $i < count($result); $i++) {
								$game = $result[$i];
						?>
						<tr class="game_item" id="<?= $game['IDGame'] ?>">
							<td><?= ($i+1)  ?></td>
							<td><?= $game['GameName']  ?></td>
							<td><?= $game['ModName']   ?></td>
							<td><?= $game['MapName']   ?></td>
							<td><?= to_local_date($game['GameDate'], $_SESSION['Player']['TimeZone'])  ?></td>
							<td><?= to_local_date($game['LoadDate'], $_SESSION['Player']['TimeZone'])  ?></td>
							<td><?= $game['Minutes']   ?></td>
							<td><?= $game['Seconds']   ?></td>
							<td><?= $game['PlayerName']?></td>
						</tr>
						<?php
							}
						?>
					</table>
					
					<?php
						} else {
					?>
					На данный момент здесь нет ни одной игры
					<?php
						}
					?>
				
					
				</form>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>