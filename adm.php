<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Панель администрирования</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/adm.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна yahoo:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
		<script type="text/javascript" src="js/pages/adm.js"></script>
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
				<fieldset>
					<legend>Слияние игроков</legend>
					
					<select name="acceptor"></select>
					<<
					<select name="donor"></select>
					&nbsp;
					<input name="merge" type="button" value="Слить" />
				</fieldset>
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>