<?php
	// запускаем сессию:
	session_start();
	// подключаем основную библиотеку скриптов:
	require_once 'requires/common.php';
	// проверяем
	if (isset($_POST['action'])) {
		$action = get_param('action');
		switch ($action) {
			case 'login':	if (isset($_SESSION['Player']) && $_SESSION['Player']['ID'])
								die('{error:"1",message:"Вы уже авторизованы!"}');
							$login    = trim(get_param('login'));
							$password = get_param('password');
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
							$login            = trim(get_param('login'));
							$password         = get_param('password');
							$confirm_password = get_param('confirm_password');
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
			
			
			
			// :DEBUG:
			case 'session':	print_r($_SESSION);
							break;
			
			// :DEBUG:
			case 'delete':	// очистка от тестовых игр:
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
			
			default: 		;
			
		}
	}
?>