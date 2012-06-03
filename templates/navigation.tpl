					<li class="nav_header">Навигация</li>
					<?php
						// вытаскиваем из сессии список доступных для посещения страниц:
						$pages = $_SESSION['Player']['Navigation'];
						for ($i=0; $i < count($pages); $i++)
							echo("<li><a href='".$pages[$i]['Name']."'>".$pages[$i]['Comment']."</a></li>");
					?>