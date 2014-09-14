<?php
	// подключаем основную библиотеку скриптов:
	include 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Главная страница</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		
		<?php
			// подключаем диалоговые окна:
			require_once 'templates/jqueryui.tpl';
		?>
		
		<script type="text/javascript" src="js/pages/common.js"></script>
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
				<!--<h1>Друзья!</h1>
				<p>Всем из нас.....! Всем из нас, кому небезразлично собственное мастерство в игре. Всем из нас, для кого идея самосовершенствования, как бойца RWG - не пустой звук. Каждому, для кого RWG чуть чуть больше, чем игра - посвящается данный ресурс.</p>
				<p>Здесь вы найдете возможность получить в свои руки мощнейший технический инструмент для оценки качества игр, рейтинги игроков по различным системам. Вы сможете увидеть свои результаты в развитии, увидеть - стали вы лучше или хуже играть, получить возможность разобраться - в чем дело.</p>
				<p>Вас приветствует команда разработчиков системы оценки боев, создатели различных рейтинговых систем. Создавая этот сайт мы хотели одного - что бы каждый нашел на нем для себя возможность стать лучшим - лучшим игроком в сообществе, лучшим танкистом, разведчиком, стратегом или тактиком.</p>
				<h1>ВНИМАНИЕ!</h1>
				<p>Для того, чтобы иметь возможность заливать игры, вы должны <span class="login">авторизоваться</span>. Если у Вас ещё нет учётной записи, <span class="register">зарегистрируйтесь</span>!</p>-->
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>