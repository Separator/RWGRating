					<li class="nav_header">Навигация</li>
					<li><a href="index.php">Главная страница</a></li>
					<li><a href="instructions.php">Инструкция</a></li>
					<li><a href="rating.php">Рейтинг игроков</a></li>
					<li><a href="games.php">Список игр</a></li>
					<? if ($_SESSION['Player']['ID']): ?>
					<li><a href="load.php">Залить игру</a></li>
					<li><a href="cabinet.php">Личный кабинет</a></li>
					<li><a href="player.php">Ваши характеристики</a></li>
					<? endif ?>