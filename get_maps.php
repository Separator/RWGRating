<?php
	require_once 'requires/common.php';
	if (isset($_POST['map'])) {
		get_zip_list('maps',$_POST['map'], 'maps.zip');
	} else {
		echo('Список файлов не передан!');
	}
?>