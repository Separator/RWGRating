<?php
	// запускаем сессию:
	session_start();
	// подключаем настройки:
	require_once 'requires/config.php';
	// подключаем библиотеку для работы с рейтингами:
	require_once 'requires/rating.php';
	// подключаем основную библиотеку скриптов:
	require_once 'requires/common.php';
	// проверяем
	if (isset($_POST['action'])) {
		$action = get_param('action');
		switch ($action) {
			case 'login':	if (isset($_SESSION['Player']) && $_SESSION['Player']['ID'])
								die('{error:"1",message:"Вы уже авторизованы!"}');
							$login    = trim($_REQUEST['login']);
							$password = $_REQUEST['password'];
							if (!$login || !$password)
								die('{error:"2",message:"Не указано имя пользователя или пароль!"}');
							$req_id = db_connect();
							$query  = req_player($login, $password);
							$result = mysql_query($query, $req_id)
								or die('{error:"3",message:"Ошибка БД!"}');
							if (mysql_num_rows($result) == 0)
								die('{error:"4",message:"Неверно указано имя пользователя или пароль!"}');
							// получаем данные по пользователю:
							$user_data = get_req_data($result);
							log_in($user_data[0]['IDPlayer'], $login, $user_data[0]['TimeZoneOffset']);
							echo('{error:"0",message:"Поздравляем, вы успешно авторизовались!"}');
							break;
			
			case 'logout':	log_out();
							echo('{error:"0",message:"Выход осуществлён"}');
							break;
			
			case 'captcha':	$_SESSION['captcha'] = captcha_digit();
							captcha_create($_SESSION['captcha']);
							echo(gif_to_base64());
							break;
			
			case 'register':if (isset($_SESSION['Player']) && $_SESSION['Player']['ID'])
								die('{error:"1",message:"Вы авторизованы!"}');
							$login            = trim($_REQUEST['login']);
							$password         = $_REQUEST['password'];
							$confirm_password = $_REQUEST['confirm_password'];
							$captcha          = get_param('captcha');
							$timezone          = get_param('timezone') or '0';
							
							if (!$login)
								die('{error:"2",message:"Не указано имя пользователя!"}');
							if (!$password)
								die('{error:"2",message:"Не указан пароль!"}');
							if ($confirm_password != $password)
								die('{error:"3",message:"Пароли не совпадают!"}');
							if ($_SESSION['captcha'] != $captcha)
								die('{error:"4",message:"Капча не верна!"}');
							// проверяем, есть ли игрок с заданным именем:
							$query = req_player_by_name($login);
							$req_id = db_connect();
							$result = mysql_query($query, $req_id);
							if (mysql_num_rows($result)) {
								$user_data = get_req_data($result);
								// если пользователь учтен, но не зарегистрирован:
								if ($user_data[0]['Password'] == '1') {
									$query  = req_reg_recorded_user($user_data[0]['IDPlayer'], $password, $timezone);
									$result = mysql_query($query, $req_id)
										or die('{error:"5",message:"Ошибка запроса регистрации учтенного игрока!"}');
									// логинимся:
									log_in($user_data[0]['IDPlayer'], $login, $timezone);
									die('{error:"0",message:"Поздравляю, вы зарегистрированы!"}');
								} else
									die('{error:"6",message:"Игрок с заданным именем уже зарегистрирован!"}');
							} else {
								$query  = req_reg_unrecorded_player_1($login, $password, $timezone);
								$result = mysql_query($query, $req_id);
								$id = mysql_insert_id($req_id);
								$query  = req_reg_unrecorded_player_2($id);
								$result = mysql_query($query, $req_id);
								log_in($id, $login, $timezone);
								echo('{error:"0",message:"Поздравляю, вы успешно зарегистрировались!"}');
							}
							break;
			
			case 'games':	$author  = get_param('author');
							$mod     = get_param('mod');
							$map     = get_param('map');
							$number  = get_param('number');
							$segment = get_param('segment');
							if ($number === "" || $segment === "")
								die('{"error":1, "message": "Не указан номер или сегмент", "data":{}}');
							// подключаемся к бд:
							$req_id = db_connect();
							// формируем данные для запроса списка игр:
							$restrictions = array('limit' => array(0=>$number, 1=>$segment));
							if ($author != "") $restrictions['SP.IDPlayer'] = $author;
							if ($mod    != "") $restrictions['SMD.IDMod'  ] = $mod;
							if ($map    != "") $restrictions['SM.IDMap'   ] = $map;
							$query  = req_games($restrictions);
							// запрос на получение списка игр:
							$result = mysql_query($query, $req_id);
							if (!mysql_num_rows($result))
								die('{"error":2, "message": "Не найдено ни одной игры", "data":{}}');
							$result = get_req_data($result);
							// формируем json:
							$json = '{"error": 0, "message": "Получен список игр", "data":{';
							for ($i=0; $i < count($result); $i++) {
								$game = $result[$i];
								$json .= "\"{$game['IDGame']}\": {";
								$json .= "\"Name\": \"{$game['GameName']}\", ";
								$json .= "\"Mod\": \"{$game['ModName']}\", ";
								$json .= "\"Map\": \"{$game['MapName']}\", ";
								$json .= '"GameDate": "'.to_local_date($game['GameDate'], $_SESSION['Player']['TimeZone']).'",';
								$json .= '"LoadDate": "'.to_local_date($game['LoadDate'], $_SESSION['Player']['TimeZone']).'",';
								$json .= "\"Minutes\": \"{$game['Minutes']}\", ";
								$json .= "\"Seconds\": \"{$game['Seconds']}\", ";
								$json .= "\"Author\": \"{$game['PlayerName']}\"},";
							}
							$json = substr($json, 0, strlen($json)-1);
							$json .= '}}';
							echo($json);
							break;
							
			// работа с комментариями:
			case 'get_comments':	$restrictions = array(
										'IDComment' => get_param('idcomment'),
										'IDPlayer'  => get_param('idplayer'),
										'IDGame'    => get_param('idgame')
									);
									$number  = get_param('number');
									$segment = get_param('segment');
									if ($number === '' || $segment === '')
										die('{"error":"1","message":"Не указаны ограничения на список комментариев","data":{}}');
									$limit = array($number, $segment);
									// запрос на получение списка комментов:
									$req_id = db_connect();
									$query  = req_game_comments($restrictions, $limit);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"2","message":"Ошибка запроса списка комментариев","data":{}}');
									$result = get_req_data($result);
									// формируем json:
									$json = '{"error": 0, "message": "Получен список комментариев", "data":{';
									for ($i=0; $i < count($result); $i++) {
										$comment = $result[$i];
										$json .= "\"{$comment['IDComment']}\": {";
										$json .= "\"IDComment\": \"{$comment['IDComment']}\", ";
										$json .= "\"IDPlayer\": \"{$comment['IDPlayer']}\", ";
										$json .= "\"Comment\": \"{$comment['Comment']}\", ";
										$json .= "\"IDGame\": \"{$comment['IDGame']}\", ";
										$json .= "\"Date\": \"{$comment['Date']}\"},";
									}
									$json = substr($json, 0, strlen($json)-1);
									$json .= '}}';
									echo($json);
									break;
			
			case 'edit_comment':	if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									$idcomment = get_param('idcomment');
									$idplayer  = $_SESSION['Player']['ID'];
									$comment   = get_param('comment');
									if ($idcomment === '')
										die('{"error":"2","message":"Не указан идентификатор комментария"}');
									if ($comment === '')
										die('{"error":"3","message":"Указан пустой комментарий"}');
									// запрос на редактирование комментария:
									$req_id = db_connect();
									$query  = req_edit_comment($idcomment, $idplayer, $comment);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"4","message":"Ошибка редактирования"}');
									echo('"error":"0","message":"Комментарий успешно обновлён"');
									break;
									
			case 'delete_comment':	if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									$idplayer  = $_SESSION['Player']['ID'];
									$idcomment = get_param('idcomment');
									if ($idcomment === '')
										die('{"error":"2","message":"Не указан идентификатор комментария"}');
									// запрос на удаление комментария:
									$req_id = db_connect();
									$query  = req_delete_comment($idcomment, $idplayer);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"3","message":"Ошибка удаления"}');
									echo('"error":"0","message":"Комментарий успешно удалён"');
									break;
			
			case 'create_comment':	if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									$idplayer  = $_SESSION['Player']['ID'];
									$idgame    = get_param('idgame');
									$comment   = get_param('comment');
									if ($idgame === '')
										die('{"error":"2","message":"Не указан идентификатор игры"}');
									if ($comment === '')
										die('{"error":"3","message":"Не указан комментарий"}');
									// запрос на создание комментария:
									$req_id = db_connect();
									$query  = req_create_comment($idgame, $idplayer, $comment);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"4","message":"Ошибка записи комментария"}');
									echo('"error":"0","message":"Комментарий успешно добавлен"');
									break;
			
			case 'check_login':		$login = get_param('login');
									if (!$login)
										die('{"error":"1","message":"Не указан логин"}');
									if (un_login($login))
										die('{"error":"2","message":"Данный логин уже используется"}');
									echo('{"error":"0","message":"Логин свободен!"}');
									break;
			case 'change_login':	$login = get_param('login');
									if (!$login)
										die('{"error":"1","message":"Не указан логин"}');
									if (un_login($login))
										die('{"error":"2","message":"Данный логин уже используется"}');
									if (!$_SESSION['Player']['ID'])
										die('{"error":"3","message":"Вы не авторизованы"}');
									$idplayer  = $_SESSION['Player']['ID'];
									$req_id = db_connect();
									$query  = req_change_login($idplayer, $login);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"4","message":"Ошибка изменения логина!"}');
									echo('{"error":"0","message":"Логин успешно изменён!"}');
									$_SESSION['Player']['Name'] = $login;
									break;
			
			case 'change_password':	if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									$idplayer  = $_SESSION['Player']['ID'];
									$password = get_param('password');
									$confirm_password = get_param('confirm_password');
									if (!($password && $confirm_password))
										die('{"error":"2","message":"Не указан пароль!"}');
									if ($password !== $confirm_password)
										die('{"error":"3","message":"Пароли не совпадают!"}');
									$req_id = db_connect();
									$query  = req_change_password($idplayer, $password);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"4","message":"Ошибка изменения пароля!"}');
									echo('{"error":"0","message":"Пароль успешно изменён!"}');
									break;
			
			case 'get_not_reg':		// получить список учтенных, но не зарегистрированных пользователей:
									$req_id = db_connect();
									$query  = req_get_unregistered();
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"1","message":"Не удалось получить список"}');
									$result = get_req_data($result);
									echo('{"error":"0","message":"Список пользователей успешно получен","users":'.json_encode($result).'}');
									break;
			
			case 'delete_game':		// удалить игру с заданным идентификатором:
									if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									// проверка на авторство:
									$id = get_param('id');
									$req_id = db_connect();
									$query  = req_game_by_id_simple($id);
									$result = mysql_query($query, $req_id);
									if (!$result)
										die('{"error":"2","message":"Нет такой игры"}');
									$result = get_req_data($result);
									if ($result[0]['IDPlayer'] != $_SESSION['Player']['ID'] && !is_admin($_SESSION['Player']['ID']))
										die('{"error":"3","message":"Вы не являетесь автором этой игры"}');
									delete_game($id);
									echo('{"error":"0","message":"Игра успешно удалена"}');
									break;
			
			case 'get_maps_list':	$idmod = get_param('idmod');
									if (!$idmod)
										die('{"error":"1","message":"Не указан мод"}');
									$req_id = db_connect();
									$query  = req_maps_by_mod($idmod);
									$result = mysql_query($query, $req_id);
									$result = get_req_data($result);
									if (!$result)
										die('{"error":"2","message":"Для данного мода нет ни одной карты"}');
									$json = '{"error":"0", "maps":{';
									$delim = '';
									foreach ($result as $key => $val) {
										$json .= $delim.'"'.$val['IDMap'].'":"'.$val['Name'].'"';
										$delim = ',';
									}
									$json .= '}}';
									echo $json;
									break;
			
			case 'merge_players':	if (!$_SESSION['Player']['ID'])
										die('{"error":"1","message":"Вы не авторизованы"}');
									if (!is_admin($_SESSION['Player']['ID']))
										die('{"error":"2","message":"Вы не являетесь администратором"}');
									$donor    = get_param('donor');
									$acceptor = get_param('acceptor');
									if (!$donor || !$acceptor)
										die('{"error":"3","message":"Не указаны необходимые данные"}');
									if ($donor == $acceptor)
										die('{"error":"4","message":"Сливаемые игроки не могут быть одинаковыми"}');
									$req_id = db_connect();
									// передаем данные от донора:
									$query  = "update stat_games set IDPlayer=$acceptor where IDPlayer=$donor";
									$result = mysql_query($query, $req_id);
									$query  = "update stat_game_comments set IDPlayer=$acceptor where IDPlayer=$donor";
									$result = mysql_query($query, $req_id);
									$query  = "update stat_player_stats set IDPlayer=$acceptor where IDPlayer=$donor";
									$result = mysql_query($query, $req_id);
									$query  = "update stat_ratings set Author=$acceptor where Author=$donor";
									$result = mysql_query($query, $req_id);
									// смотрим пароль донора и принимающего:
									$result = mysql_query("select * from stat_players where IDPlayer=$acceptor", $req_id);
									$pl1 = get_req_data($result);
									$result = mysql_query("select * from stat_players where IDPlayer=$donor", $req_id);
									$pl2 = get_req_data($result);
									if ($pl1[0]['Password'] == '1') {
										$query  = "update stat_players set Password='{$pl2[0]['Password']}' where IDPlayer=$acceptor";
										mysql_query($query, $req_id);
									}
									// передать права донора принимающему:
									$result = mysql_query("select * from stat_players_by_types where IDPlayer=$acceptor", $req_id);
									$pl1 = get_req_data($result);
									$result = mysql_query("select * from stat_players_by_types where IDPlayer=$donor", $req_id);
									$pl2 = get_req_data($result);
									$rights = array();
									foreach ($pl2 as $pl2Key => $pl2Val) {
										$transmit = true;
										foreach ($pl1 as $pl1Key => $pl1Val)
										if ($pl2Val['IDPlayerType'] == $pl1Val['IDPlayerType'])
											$transmit = false;
										if ($transmit)
											$rights[$pl2Val['IDPlayerType']] = $pl2Val['IDPlayerType'];
									}
									foreach ($rights as $rKey => $rVal) {
										$query = "insert into stat_players_by_types set IDPlayer=$acceptor, IDPlayerType=$rKey";
										mysql_query($query, $req_id);
									}
									// удаляем донора:
									$query  = "delete from stat_players where IDPlayer=$donor";
									$result = mysql_query($query, $req_id);
									$query  = "delete from stat_players_by_types where IDPlayer=$donor";
									$result = mysql_query($query, $req_id);
									// пересчитываем рейтинги:
									recalc_ratings();
									// и собсна усе:
									die('{"error":"0","message":"Пользователи успешно соединены!"}');
									break;
			
			case 'players_list':	$req_id = db_connect();
									$query  = req_players();
									$result = mysql_query($query, $req_id);
									$result = get_req_data($result);
									if (!$result)
										die('{"error":"1","message":"Не найден ни один пользователь"}');
									$json = '{"error":"0", "players":{';
									$delim = '';
									foreach ($result as $key => $val) {
										$json .= $delim.'"'.$val['IDPlayer'].'":"'.$val['Name'].'"';
										$delim = ',';
									}
									$json .= '}}';
									echo $json;
									break;
			
			
			// :DEBUG:
			case 'session':	if (!$_SESSION['Player']['ID'])
								die('{"error":"1","message":"Вы не авторизованы"}');
							if (!is_admin($_SESSION['Player']['ID']))
								die('{"error":"2","message":"Вы не являетесь администратором"}');
							print_r($_SESSION);
							break;
			
			// :DEBUG:
			case 'calc':	if (!$_SESSION['Player']['ID'])
								die('{"error":"1","message":"Вы не авторизованы"}');
							if (!is_admin($_SESSION['Player']['ID']))
								die('{"error":"2","message":"Вы не являетесь администратором"}');
							// считаем рейтинг:
							$base = new RWGDBaseWork(
								$base_settings['host'],
								$base_settings['base'],
								$base_settings['user'],
								$base_settings['password']
							);
							// 2P рейтинг:
							$rating = new DualDeploymentRating($base, 1);
							echo($rating->calculate_games());
							break;
			
			// :DEBUG:
			case 'delete':	if (!$_SESSION['Player']['ID'])
								die('{"error":"1","message":"Вы не авторизованы"}');
							if (!is_admin($_SESSION['Player']['ID']))
								die('{"error":"2","message":"Вы не являетесь администратором"}');
							// очистка от тестовых игр:
							$req_id = db_connect();
							$query  = "delete from stat_teams";
							mysql_query($query, $req_id);
							$query  = "delete from stat_player_stats";
							mysql_query($query, $req_id);
							$query  = "delete from stat_players_by_types where not IDPlayer=2";
							mysql_query($query, $req_id);
							$query  = "delete from stat_players where not IDPlayer=2";
							mysql_query($query, $req_id);
							$query  = "delete from stat_games_images";
							mysql_query($query, $req_id);
							$query  = "delete from stat_games";
							mysql_query($query, $req_id);
							echo('Данные уничтожены');
							break;
			
			default: 		die('{"error":"1","message":"Неизвестный запрос!"}');
			
		}
	}
?>