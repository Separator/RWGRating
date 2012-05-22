<?php
	// $.ajax({url:'ajax.php',type:'POST',data:{action:'session'}})
	// $.ajax({url:'ajax.php',type:'POST',data:{action:'delete'}})
	// функции работы с MySQL:
	// подключаемся к БД:
	function db_connect() {
		$db_name = "separ614";
		$db_host = "localhost";
		//$db_host = "sql-4.radyx.ru:3306";
		$db_user = "separ614";
		$db_pass = "sh06e0t4do";
		
		$link_id = mysql_connect($db_host, $db_user, $db_pass);
		mysql_select_db($db_name, $link_id);
		return $link_id;
	}
	// получаем строку запроса на пользователя с заданным логином и паролем:
	function req_player($login, $password) {
		return "select * from stat_players where Name='$login' and Password='".md5($password)."'";
	}
	// получить строку запроса на выборку пользователя с заданным именем:
	function req_player_by_name($login) {
		return "select * from stat_players where Name='$login'";
	}
	// регистрация учтенного пользователя:
	function req_reg_recorded_user($id, $password, $timezone) {
		return "update stat_players set Password='".md5($password)."', TimeZoneOffset=$timezone where IDPlayer=$id";
	}
	// запрос на учёт пользователя:
	function req_calculate_user_1($login) {
		return "insert into stat_players set Name='$login', Password='1', TimeZoneOffset=0";
	}
	// первый запрос регистрации неучтённого пользователя:
	function req_reg_unrecorded_player_1($login, $password, $timezone) {
		return "insert into stat_players set Name='$login', Password='".md5($password)."', TimeZoneOffset=$timezone";
	}
	// второй запрос регистрации неучтённого пользователя:
	function req_reg_unrecorded_player_2($id, $type=2) {
		return "insert into stat_players_by_types set IDPlayer=$id, IDPlayerType=$type";
	}
	// получаем строку запроса на список страниц для заданного типа пользователя:
	function req_pages_by_type($player_type) {
		return "SELECT SP.Name FROM stat_player_types AS SPT ".
		"INNER JOIN stat_pages_by_types AS SPBT ON SPT.IDPlayerType = SPBT.IDPlayerType ".
		"INNER JOIN stat_pages as SP on SPBT.IDPage=SP.IDPage ".
		"WHERE SPT.IDPlayerType =$player_type";
	}
	// получаем строку запроса на список страниц для заданного идентификатора пользователя:
	function req_pages_by_player_id($id) {
		return "SELECT SP.Name FROM stat_players AS SPl ".
		"INNER JOIN stat_players_by_types AS SPlBT ON SPl.IDPlayer = SPlBT.IDPlayer ".
		"INNER JOIN stat_player_types AS SPT ON SPT.IDPlayerType = SPlBT.IDPlayerType ".
		"INNER JOIN stat_pages_by_types AS SPBT ON SPT.IDPlayerType = SPBT.IDPlayerType ".
		"INNER JOIN stat_pages AS SP ON SPBT.IDPage = SP.IDPage ".
		"WHERE SPl.IDPlayer =$id";
	}
	// список доступных модов противостояния:
	function req_mods() {
		return "select * from stat_mods order by Name desc";
	}
	// вытащить мод по айди:
	function req_mod_by_id($id) {
		return "select * from stat_mods where IDMod=$id";
	}
	// список карт для различных модов:
	function req_maps() {
		return "select IDMap, Name from stat_maps order by IDMod asc, Name asc";
	}
	// вытащить карту по айди:
	function req_map_by_id($id) {
		return "select * from stat_maps where IDMap=$id";
	}
	// существование игры с заданным хэшем:
	function req_game_exist($md5hash) {
		return "select IDGame from stat_games where MD5='$md5hash'";
	}
	//записать игру:
	function req_create_game() {
		$user = $_SESSION['Player'];
		$game = $_SESSION['Game'];
		return "insert into stat_games set ".
		"IDPlayer={$user['ID']}, ".
		"Name='{$game['game_name']}', ".
		"MD5='{$game['stat_file']['MD5']}', ".
		"Minutes={$game['stat_file']['Time']['Minutes']}, ".
		"Seconds={$game['stat_file']['Time']['Seconds']}, ".
		"Date={$game['game_date']}, ".
		"IDMap={$game['game_map']}, ".
		"IDMod={$game['game_mode']}, ".
		"LoadDate=".time();
	}
	// записать рисунок статы игры:
	function req_create_game_img($id, $name) {
		return "insert into stat_games_images set IDGame=$id, Name='$name'";
	}
	// записать комментарий для заданной игры:
	function req_create_comment($id_game, $id_player, $comment) {
		return "insert into stat_game_comments set IDGame=$id_game, IDPlayer=$id_player, Comment='$comment', Date=".time();
	}
	
	// записать "команду":
	function req_create_team($win, $id, $num, $rep) {
		return "insert into stat_teams set Win=$win, IDGame=$id, Number=$num, ReplayFile='$rep'";
	}
	// записать стату игрока:
	function req_create_player_stat($idplayer, $idteam, $stats) {
		$result = "insert into stat_player_stats set ".
		"IDPlayer=$idplayer, IDTeam=$idteam";
		// убираем и переопределяем некоторые ключи:
		unset($stats['Name']);
		if ($stats['Wathcer'])	$stats['Wathcer'] = 'true';
		else					$stats['Wathcer'] = 'false';
		// формируем запрос:
		foreach ($stats as $k=>$v)
			$result .= ", $k=$v";
		return $result;
	}
	// получить данные по игре по id:
	function req_game_by_id($id) {
		return "select SG.Name as GameName, SG.Minutes as Minutes, ".
		"SG.Seconds as Seconds, SG.Date as GameDate, SG.LoadDate as LoadDate, ".
		"SP.IDPlayer as IDPlayer, SP.Name as PlayerName, ".
		"SM.IDMap as IDMap, SM.Name as MapName, SM.MapFile as MapFile, ".
		"SMD.IDMod as IDMod, SMD.Name as ModName ".
		"from stat_games as SG ".
		"inner join stat_players as SP on SG.IDPlayer=SP.IDPlayer ".
		"inner join stat_maps as SM    on SG.IDMap=SM.IDMap ".
		"inner join stat_mods as SMD   on SG.IDMod=SMD.IDMod ".
		"where SG.IDGame=$id";
	}
	
	// получить список игр с заданными ограничениями:
	function req_games($dependences) {
		foreach ($dependences as $k => $v)
		if (!$v) unset($dependences[$k]);
		$result = "select SG.IDGame as IDGame, SG.Name as GameName, SG.Minutes as Minutes, ".
		"SG.Seconds as Seconds, SG.Date as GameDate, SG.LoadDate as LoadDate, ".
		"SP.IDPlayer as IDPlayer, SP.Name as PlayerName, ".
		"SM.IDMap as IDMap, SM.Name as MapName, SM.MapFile as MapFile, ".
		"SMD.IDMod as IDMod, SMD.Name as ModName ".
		"from stat_games as SG ".
		"inner join stat_players as SP on SG.IDPlayer=SP.IDPlayer ".
		"inner join stat_maps as SM    on SG.IDMap=SM.IDMap ".
		"inner join stat_mods as SMD   on SG.IDMod=SMD.IDMod ";
		if (count($dependences)) {
			$count = 0;
			foreach ($dependences as $k => $v) {
				$type  = $k;
				$value = $v;
				if ($type == 'limit') {
					$result .= "order by GameDate asc ";
					$result .= "limit {$value[0]}, {$value[1]}";
				} else {
					if ($count)	$result .= "and ";
					else		$result .= "where ";
					$result .= "$k=$value ";
				}
				$count++;
			}
		}
		return $result;
	}
	
	// получить группы по идентификатору игры:
	function req_teams_by_game_id($id) {
		return "select * from stat_teams as SG ".
		"inner join stat_player_stats as SPS on SG.IDTeam=SPS.IDTeam ".
		"inner join stat_players as SP on SPS.IDPlayer=SP.IDPlayer ".
		"where SG.IDGame=$id order by SG.Number asc";
	}
	
	// получить двумерный массив из результата запроса к БД:
	function get_req_data($req_data) {
		for ($data=array(); $row=mysql_fetch_assoc($req_data); $data[]=$row);
		return $data;
	}
	
	// получить список авторов:
	function get_authors() {
		return "SELECT sp.IDPlayer as IDPlayer, sp.Name as Name FROM stat_games as sg
		inner join stat_players as sp on sg.IDPlayer=sp.IDPlayer
		group by IDPlayer";
	}
	
	// получить список комментариев для заданной игры:
	function req_game_comments($restrictions=array(), $limit=array(0, 10)) {
		// удаляем пустые присланные параметры:
		foreach ($restrictions as $k => $v)
		if (!$v) unset($restrictions[$k]);
		// формируем строку запроса:
		$result = 'select * from stat_game_comments ';
		if (count($restrictions)) {
			$result .= 'where ';
			$buffer = array();
			foreach ($restrictions as $k => $v)
				$buffer[] = $k . '=' . $v;
			$result .= join($buffer, ', ');
		}
		if (count($limit)) {
			$result .= " limit {$limit[0]}, {$limit[1]}";
		}
		return $result . ';';
	}
	
	// возвращает данные без запрещённых символов:
	function imp_exp_data($data, $arr) {
		// удаляем из переданного параметра:
		foreach ($arr as $k => $v) {
			$data = explode($k, $data);
			$data = implode($v, $data);
		}
		return $data;
	};
	
	function get_param($param, $type="gpc") {
		// задаём список заменяемых символов:
		$rep = array(
			"&" => "&amp;",
			"-" => "&minus;",
			"'" => "&apos;",
			"\"" =>"&quot;",
			"/" => "&frasl;",
			"∗" => "&lowast;",
			"<" => "&lt;",
			">" => "&gt;"
		);
		
		// выполняем проверки в зависимости от типа полученных данных:
		switch ($type) {
			case "file":	if (!isset($_FILES[$param])) return "";
							return $_FILES[$param];
							break;
			case "date":	if (!isset($_REQUEST[$param])) return "";
							unset($rep["/"]);
							return imp_exp_data($_REQUEST[$param], $rep);
							break;
			case "gpc":		if (!isset($_REQUEST[$param])) return "";
							return imp_exp_data($_REQUEST[$param], $rep);
							break;
			default:		return "";
		}
	}
	
	function log_out() {
		if (!$_SESSION['Player']['ID'])
			die('{error:"1",message:"Вы ещё не авторизованы!"}');
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) unset($_COOKIE[session_name()]);
		session_destroy();
	}
	
	function log_in($id, $login, $timezone) {
		// идентификатор типа пользователя "Гость":
		$guest_type_id = 1;
		
		// заносим данные пользователя в сессию:
		$_SESSION['Player'] = array('ID'=>$id, 'Name'=>$login, 'TimeZone'=>$timezone, AvailPages=>array());
		// необходимо получить набор страниц, доступных для данного пользователя:
		// для всех пользователей:
		$req_id = db_connect();
		$request = req_pages_by_type($guest_type_id);
		$result = get_req_data(mysql_query($request, $req_id));
		for ($i=0; $i < count($result); $i++)
			$_SESSION['Player']['AvailPages'][] = $result[$i]['Name'];
		// для авторизованных пользователей:
		if ($id) {
			$request = req_pages_by_player_id($id);
			$result = get_req_data(mysql_query($request));
			for ($i=0; $i < count($result); $i++)
				$_SESSION['Player']['AvailPages'][] = $result[$i]['Name'];
		}
		return true;
	}
	
	// получение капчи:
	// получение base64-кода gif-изображения:
	function gif_to_base64($file='images/captcha.gif') {
		if($fp = fopen($file,"rb", 0)) {
			$picture = fread($fp,filesize($file));
			fclose($fp);
			$base64 = chunk_split(base64_encode($picture));
			return 'data:image/gif;base64,'.$base64;
		} else
			return '';
	}
	// собственно само содержание капчи:
	function captcha_digit($length=4) {
		$result = '';
		for ($i=0; $i < $length; $i++)
			$result .= mt_rand(0, 9);
		return $result;
	}
	// создание файла капчи:
	function captcha_create($text, $file='images/captcha.gif', $font='fonts/cour.ttf') {
		$font_size = 20;
		$paddingX  = 15;
		$paddingY  = 36;
		$img       = imagecreatefromgif('images/captcha_background.gif');
		$color     = imagecolorallocate($img, 255, 255, 255);
		for ($i=0; $i < strlen($text); $i++, $paddingX += $font_size) {
			imagettftext($img, $font_size, mt_rand(0,40)-10, $paddingX, $paddingY, $color, $font, $text[$i]);
		};
		imageGif($img, $file);
		imagedestroy($img);
	}
	
	
	// разбор файла статы:
	// вытащить из массива значение с заданным индексом:
	function get_by_index($arr, $index) {
		return $arr[$index];
	}
	
	// перевести данные в удобный вид:
	function change_stat_struct($stat) {
		$result =  array(
			'Name'              => $stat['username'],
			'InfantryKills'     => get_by_index(explode("/", $stat['infantry']),     0),
			'InfantryLoses'     => get_by_index(explode("/", $stat['infantry']),     1),
			'TanksKills'        => get_by_index(explode("/", $stat['tanks']),        0),
			'TanksLoses'        => get_by_index(explode("/", $stat['tanks']),        1),
			'TrucksKills'       => get_by_index(explode("/", $stat['trucks']),       0),
			'TrucksLoses'       => get_by_index(explode("/", $stat['trucks']),       1),
			'AircraftKills'     => get_by_index(explode("/", $stat['aircrafts']),    0),
			'AircraftLoses'     => get_by_index(explode("/", $stat['aircrafts']),    1),
			'AntiAircraftKills' => get_by_index(explode("/", $stat['antiaircraft']), 0),
			'AntiAircraftLoses' => get_by_index(explode("/", $stat['antiaircraft']), 1),
			'ArtilleryKills'    => get_by_index(explode("/", $stat['artillery']),    0),
			'ArtilleryLoses'    => get_by_index(explode("/", $stat['artillery']),    1),
			'TrainsShipsKills'  => get_by_index(explode("/", $stat['trainsships']),  0),
			'TrainsShipsLoses'  => get_by_index(explode("/", $stat['trainsships']),  1),
			'UnknownKills'      => get_by_index(explode("/", $stat['unknown']),      0),
			'UnknownLoses'      => get_by_index(explode("/", $stat['unknown']),      1)
		);
		// проходим по всем ключам созданного массива и смотрим на наличие очков:
		$points = 0;
		for (reset($result); list($k, $v)=each($result);)
			$points += $v;
		if ($points)	$result['Wathcer'] = false;
		else			$result['Wathcer'] = true;
		return $result;
	}
	
	// вытаскиваем данные по игре из xml-файла + проверяем их
	function xml_file_to_arr($file) {
		$root = simplexml_load_file($file);
		$result = array(
			'Time'  => array(
				'Minutes' => "{$root->time->minutes}",
				'Seconds' => "{$root->time->seconds}",
			),
			'MD5'   => "{$root->md5}",
			'Teams' => array()
		);
		$team = $root->teams->team;
		foreach ($team as $nn) {
			$players = array();
			foreach ($nn->player as $pl) {
				$player = array();
				foreach ($pl as $plkey=>$plval)
					$player["{$plkey}"] = "{$plval}";
				$players[] = change_stat_struct($player);
			}
			$result['Teams'][] = array('Index'=>"{$nn[index]}", 'Players'=>$players);
		}
		
		// минимальные проверки полученных данных на корректность:
		if (!$result['Time'] || !count($result['Time'])) return false;
		if (!is_numeric($result['Time']['Minutes'])) return false;
		if (!is_numeric($result['Time']['Seconds'])) return false;
		if (strlen($result['MD5']) != 32)    return false;
		if (!count($result['Teams'])) return false;
		$md5str = $result['Time']['Minutes']."0".$result['Time']['Seconds']."0";
		
		for ($i=0; $i < count($result['Teams']); $i++) {
			if (!$result['Teams'][$i]['Index']) return false;
			if (!count($result['Teams'][$i]['Players'])) return false;
			// в любой команде должен быть хотя-бы один игрок с очками:
			$withPoints = false;
			for ($j=0; $j < count($result['Teams'][$i]['Players']); $j++) {
				if (!$result['Teams'][$i]['Players'][$j]['Wathcer']) $withPoints = true;
				$md5str .= "t{$result['Teams'][$i]['Index']}-".
				"{$result['Teams'][$i]['Players'][$j]['InfantryKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['InfantryLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['TanksKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['TanksLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['TrucksKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['TrucksLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['AircraftKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['AircraftLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['AntiAircraftKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['AntiAircraftLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['ArtilleryKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['ArtilleryLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['TrainsShipsKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['TrainsShipsLoses']}-".
				"{$result['Teams'][$i]['Players'][$j]['UnknownKills']}/".
				"{$result['Teams'][$i]['Players'][$j]['UnknownLoses']}-";
			};
			if (!$withPoints) return false;
		}
		// проверка присланного хэша:
		if (md5($md5str) != $result['MD5']) return false;
		return $result;
	}
	
	// преобразуем данные запроса статы в удобочитаемый вид:
	function convert_stats($stats) {
		$result = array();
		for ($i=0; $i < count($stats); $i++) {
			$index = $stats[$i]['Number'];
			if (!$result[$index]) {
				$result[$index] = array(
					'Players'    => array(),
					'IDTeam'     => $stats[$i]['IDTeam'],
					'Win'        => $stats[$i]['Win'],
					'IDGame'     => $stats[$i]['IDGame'],
					'Number'     => $index,
					'ReplayFile' => $stats[$i]['ReplayFile']
				);
			}
			$result[$index]['Players'][] = array(
				'Name'         => $stats[$i]['Name'],
				'Infantry'     => "{$stats[$i]['InfantryKills']}/{$stats[$i]['InfantryLoses']}",
				'Tanks'        => "{$stats[$i]['TanksKills']}/{$stats[$i]['TanksLoses']}",
				'Trucks'       => "{$stats[$i]['TrucksKills']}/{$stats[$i]['TrucksLoses']}",
				'Aircraft'     => "{$stats[$i]['AircraftKills']}/{$stats[$i]['AircraftLoses']}",
				'AntiAircraft' => "{$stats[$i]['AntiAircraftKills']}/{$stats[$i]['AntiAircraftLoses']}",
				'Artillery'    => "{$stats[$i]['ArtilleryKills']}/{$stats[$i]['ArtilleryLoses']}",
				'TrainsShips'  => "{$stats[$i]['TrainsShipsKills']}/{$stats[$i]['TrainsShipsLoses']}",
				'Unknown'      => "{$stats[$i]['UnknownKills']}/{$stats[$i]['UnknownLoses']}",
				'Wathcer'      => $stats[$i]['Wathcer']
			);
		}
		return $result;
	}

	// работа со временем:
	// получить из локального времени gmt-timestamp:
	function local_datetime_to_timestamp($date, $time, $timezone) {
		list($year, $month, $day) = explode('/', $date);
		list($hours, $minutes) = explode(':', $time);
		return mktime($hours, $minutes, 0, $month, $day, $year) - $timezone*60;
	}
	// получить из даты и смещения локальное время:
	function to_local_date($timestamp, $timezone) {
		return date("d.m.Y", $timestamp + $timezone*60);
	}
	
?>