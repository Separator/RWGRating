<html>
	<body>
		<div>
			<?php
				function getpostcookie($param, $type="gpc") {
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
					// возвращает данные без запрещённых символов:
					function imp_exp_data($data, $arr) {
						// удаляем из переданного параметра:
						foreach ($arr as $k => $v) {
							$data = explode($k, $data);
							$data = implode($v, $data);
						}
						return $data;
					};
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
			?>
		</div>
		<div>
			<form action="test.php" method="post">
				<input type="text" name="data" />
				<input type="submit" />
			</form>
			<?php
				if (isset($_REQUEST['data'])) {
					echo(getpostcookie('data'));
				} else
					echo('no data');
			?>
		</div>
	</body>
</html>