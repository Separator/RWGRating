<?php
	session_start();
	// подключаем настройки:
	include 'requires/config.php';
	// подключаем библиотеку для работы с рейтингами:
    include 'requires/rating.php';
	// подключаем основную библиотеку скриптов:
    include 'requires/common.php';
	
	// в случае, если не заданы данные пользователя, заполняем их данными по умолчанию:
	if (!isset($_SESSION['Player']) || isset($_SESSION['Player']['Logged']) && !$_SESSION['Player']['Logged'])
		log_in('', 'Гость', 0);
	// если пользователь не имеет права на работу с данной страницей - бреем его нафиг:
	$checkPage = false;
	$uri = explode('/', $_SERVER["SCRIPT_NAME"]);
	$filename = $uri[count($uri) - 1];
	for ($i=0; $i < count($_SESSION['Player']['AvailPages']); $i++)
	if ($filename == $_SESSION['Player']['AvailPages'][$i])
		$checkPage = true;
	if (!$checkPage) {
		$errorStr = "<html><head>".
		"<meta http-equiv='content-type' content='text/html; charset=UTF-8'>".
		"<link rel='stylesheet' type='text/css' media='all' href='css/common.css'>".
		"</head><body><div class='errmsg'>".
		"<table><tr><td valign='center'>".
		"Вы не имеете права на пользование данной страницей!".
		"<br /><br /><a href='index.php'>На главную</a>".
		"</td></tr></table></div></body></html>";
		die($errorStr);
	}
?>