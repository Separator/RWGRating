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