<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Личный кабинет</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/cabinet.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/cabinet.js"></script>
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
				<h1>Личный кабинет</h1>
				
				<h3>Здравствуйте, <a href="player.php"><?= $_SESSION['Player']['Name'] ?></a>!</h3>
				
				<h3>Изменить логин:</h3>
				<input type="text" name="login" />
				<input type="button" value="Проверить на занятость" class="check_login" />
				<span class="loader_wrapper"></span>
				<br />
				<input type="button" value="Изменить логин" name="change_login" />
				
				<br />
				<br />
				
				<h3>Изменить пароль:</h3>
				<input type="password" name="password" /> Новый пароль<br />
				<input type="password" name="confirm_password" /> Подтвердить новый пароль<br />
				<input type="button" value="Изменить пароль" name="change_password" />
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>