<?php
	// подключаем основную библиотеку скриптов:
	require_once 'requires/session.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>Инструкция</title>
		<link rel="stylesheet" type="text/css" media="all" href="css/common.css">
		<link rel="stylesheet" type="text/css" media="all" href="css/instructions.css">
		
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
				<h1>Инструкция по учету игр</h1>
				<h2>1. Создание файла статистики</h2>
				<p>
					Файл статистики создается с помощью программы <a href="downloads/RWGSP.rar" title="Скачать программу">RWGSP</a>.
					Эта программа позволяет переводить изображение статистики
					(из формата <a href="http://ru.wikipedia.org/wiki/BMP" target="_blank" title="О формате bmp">bmp, 24х разрядный</a>) в текстовый вид.
					Скачайте её и разархивируйте в любое удобное место.
				</p>
				
				<p>По окончанию любой игры серии "Противостояние" отображается окно статистики:</p>
				<div class="centric">
					<a href="images/01_stat.png" target="_blank">
						<img alt="" title="Окно статистики" src="images/01_stat.png" />
					</a>
					<br />
					Рисунок 1.1. Окно статистики
				</div>
				<p>
					С помощью клавиши
					<a href="http://ru.wikipedia.org/wiki/Print_Screen" target="_blank" title="О Print Screen">Print Screen</a>
					сделайте снимок экрана, затем откройте любой графический редактор,
					к примеру <a href="http://ru.wikipedia.org/wiki/Microsoft_Paint" target="_blank" title="О программе Paint">Paint</a>,
					выполните команду "Вставить" и сохраните полученный рисунок в формате
					<a href="http://ru.wikipedia.org/wiki/BMP" target="_blank" title="О формате bmp">bmp, 24х разрядный</a>.
				</p>
				
				<p style="color:red;">
					Внимание! Перед тем как делать снимок экрана, убедитесь, что курсор не перекрывает данные статистики!
				</p>
				
				<p>
					В некоторых случаях в статистике могут появляться лишние игроки. В этом случае просто перечеркните их стату, как
					это показано в следующем рисунке:
				</p>
				<div class="centric">
					<a href="images/wrong_stat.jpeg" target="_blank">
						<img alt="" title="Статистика с лишними игроками" src="images/wrong_stat.jpeg" />
					</a>
					<br />
					Рисунок 1.2. Статистика с лишними игроками
				</div>
				
				
				<p>Теперь, когда у нас есть графический файл, запускаем RWGSP.exe (находится в том архиве, что мы скачали ранее)
				и нажимаем кнопку "Открыть":</p>
				<div class="centric">
					<a href="images/02_stat.png" target="_blank">
						<img alt="" title="Окно программы RWGSP" src="images/02_stat.png" />
					</a>
					<br />
					Рисунок 1.3. Окно программы RWGSP
				</div>
				
				<p>В открывшемся диалоговом окне выбираем ранее сохранённый графический файл статистики и нажимаем "Открыть":</p>
				<div class="centric">
					<a href="images/03_stat.png" target="_blank">
						<img alt="" title="Диалоговое окно выбора файла" src="images/03_stat.png" />
					</a>
					<br />
					Рисунок 1.4. Диалоговое окно выбора файла
				</div>
				
				<p>
					Выбранный файл должен отобразиться в окне программы. Теперь осталось лишь перевести выбранный файл в текстовый вид.
					Для этого нажимаем кнопку "Извлечь":
				</p>
				<div class="centric">
					<a href="images/04_stat.png" target="_blank">
						<img alt="" title="Окно программы RWGSP с выбранным файлом статистики" src="images/04_stat.png" />
					</a>
					<br />
					Рисунок 1.5. Окно программы RWGSP с выбранным файлом статистики
				</div>
				
				<p>
					Когда прогрессбар дойдет до конца, жмём кнопку "Сохранить" и в диалоговом окне вводим название файла,
					например, "TestGame.xml" (файл обязательно должен быть в xml-формате):
				</p>
				<div class="centric">
					<a href="images/05_stat.png" target="_blank">
						<img alt="" title="Сохранение xml-файла статистики" src="images/05_stat.png" />
					</a>
					<br />
					Рисунок 1.6. Сохранение xml-файла статистики
				</div>
				
				<p>Теперь у Вас есть xml-файл статистики, и Вы можете приступать ко 2му шагу :)</p>
				
				
				<h2>2. Учет игры</h2>
				<p>
					Прежде всего, Вам необходимо зарегистрироваться. Гость не может заливать игры.
					Для того, чтобы зарегистрироваться, нажмите на ссылку "Регистрация" в правой верхней части окна:
				</p>
				<div class="centric">
					<a href="images/01_register.png" target="_blank">
						<img alt="" title="Вызов окна регистрации" src="images/01_register.png" />
					</a>
					<br />
					Рисунок 2.1. Вызов окна регистрации
				</div>
				
				<p>При нажатии на ссылку откроется окно регистрации:</p>
				<div class="centric">
					<a href="images/02_register.png" target="_blank">
						<img alt="" title="Окно регистрации" src="images/02_register.png" />
					</a>
					
					<br />
					Рисунок 2.2. Окно регистрации
				</div>
				
				<p>Заполните все необходимые поля и нажмите "Далее".</p>
				<p>Если все впорядке, Вы увидите окно подтверждения регистрации:</p>
				<div class="centric">
					<a href="images/03_register.png" target="_blank">
						<img alt="" title="Окно подтверждения регистрации" src="images/03_register.png" />
					</a>
					
					<br />
					Рисунок 2.3. Окно подтверждения регистрации
				</div>
				
				<p>После нажатия на кнопку "Ок" происходит автоматический вход.</p>
				<p>Если Вы зарегистрировались ранее, просто авторизуйтесь.</p>
				
				<p>На панели навигации выбираем пункт "Залить игру":</p>
				<div class="centric">
					<a href="images/01_upload.png" target="_blank">
						<img alt="" title="Панель навигации авторизованного пользователя" src="images/01_upload.png" />
					</a>
					<br />
					Рисунок 2.4. Панель навигации авторизованного пользователя
				</div>
				
				<p>Заполняем форму заливки игры. В поле "Статистика" выбираем сохранённый ранее файл "TestGame.xml" и нажимаем кнопку "Далее":</p>
				<div class="centric">
					<a href="images/02_upload.png" target="_blank">
						<img alt="" title="Форма заливки игры (шаг 1)" src="images/02_upload.png" />
					</a>
					<br />
					Рисунок 2.5. Форма заливки игры (шаг 1)
				</div>
				
				<p>
					Во второй форме указываем победившую команду. Также можно добавить повторы игры от разных команд.
					После этого нажимаем "Далее":
				</p>
				<div class="centric">
					<a href="images/03_upload.png" target="_blank">
						<img alt="" title="Форма заливки игры (шаг 2)" src="images/03_upload.png" />
					</a>
					<br />
					Рисунок 2.6. Форма заливки игры (шаг 2)
				</div>
				
				<p>Поздравляем, вы успешно залили свою первую игру! Надеюсь, не последнюю :)</p>
				<div class="centric">
					<a href="images/04_upload.png" target="_blank">
						<img alt="" title="Окно подтверждения заливки игры" src="images/04_upload.png" />
					</a>
					<br />
					Рисунок 2.7. Окно подтверждения заливки игры
				</div>
				
			</div>
		</div>
		
		<div class="footer b_radius">
			<?php require_once 'templates/footer.tpl'; ?>
		</div>
	</body>
</html>