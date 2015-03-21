<?php date_default_timezone_set("Europe/Moscow"); session_start();
	/* Создание заголовка не кэшируемой в браузерах страницы */
	header("Cache-Control: private");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	const SITE_DIR = '../'; // Путь к директории тестируемого сайта

	class API { // основной класс для работы
		const MODE_PROD = 0;
		const MODE_DEV = 1;

		const MESSAGE_TYPE_WARNING = 0;
		const MESSAGE_TYPE_ERROR = 1;
		const MESSAGE_TYPE_INFO = 2;

		public $tests = array(); // Список найденных тестов
		public $tobj = array (); // Сисок объектов тестов

		static public function getSiteMode() { // отдает константу MODE_PROD или MODE_DEV
			if (file_exists(SITE_DIR.'testmode.ini')) { return self::MODE_DEV; } else { return self::MODE_PROD; }
		}
		public function getTestList() { // TestPrototype[] - отдает набор объектов тестов
			$TestList = array();
			foreach ($this->tests as $test) $TestList[] = new $test;
			return $TestList;
		}
		public function getProdTestList() { // : string[] - отдает набор имен классов тестов для живого сайта
		// собираться должен так
		// return array(
    	// Test\Name123::getClass(),
    	// Test\Name456::getClass(),
    	// ...
		// )
			$ProdTestList = array();
			foreach ($this->tests as $test) if (@$test::getMode() == API::MODE_PROD) $ProdTestList[] = $test;
			return $ProdTestList;
		}
		public function getDevTestList() { // : string[] - отдает набор имен классов тестов для тестового сайта
			$DevTestList = array();
			foreach ($this->tests as $test) if (@$test::getMode() == API::MODE_DEV) $DevTestList[] = $test;
			return $DevTestList;
		}
	}

	require_once('component/sitetester.php'); // Добавить дополнительные классы
	
	$Api = new API; // Создать объект главного класса

	/* Поиск и подключение тестов */
	foreach (glob('tests/*.php') as $filename) {
		require_once($filename);
		$Api->tests[] = substr(basename($filename),0,-4);
	}

	$tobj = $Api->getTestList(); // Получение списка объектов тестов
	$ProdTestList = $Api->getProdTestList(); // Список классов тестов для живого сайта
	$DevTestList = $Api->getDevTestList(); // Список классов тестов для тестового сайта

	if (isset($_GET["run"])) { $run = true; } else { $run = false; } // Датчик запуска тестов
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Тесты сайта</title>
	<style>
		p { margin: 0px; padding: 0px; }
	</style>
</head>

<body>
	<!-- 3.1 Основной интерфейс - списковый -->
	<!-- Выводит все тесты, помечает, что не были запущены -->
	<?php if (!isset($_GET["test"])) {/******************** РЕЖИМ СПИСКА ТЕСТОВ ************************/?>

		<h1>Доступные тесты сайта</h1>
		<?php
			$i = 0;
			foreach ($Api->tests as $test) {
				echo "<div>";
				if (in_array($test,$ProdTestList)) { echo "[prod] &nbsp; &nbsp; "; } else { echo "[dev] &nbsp; &nbsp; "; }
				echo "<a href=\"?test=$test\">$test</a>";

				if ($run) {
					$color = Component\SiteTester\Status::getColor( $tobj[$i]->run() ); // Выполнить тест
					/* При запуске сделать сохранение статусов в сессию.
					Тогда можно будет просто вызвать список, а он выведет статусы, которые буди вычислены. */
					$_SESSION[$test."_stat"] = "<span style=\"color: $color\">".$_SESSION[$test."_stat"]."</span>".' ('.date("D, d M Y H:i:s").')';
				}

				if (isset($_SESSION[$test."_stat"])) {
					echo "&nbsp; &nbsp; ---- &nbsp; &nbsp;".$_SESSION[$test."_stat"];
				} else echo (' (не выполнялся)');

				echo ("</div>\r\n");
				$i++;
			}
		?>
		<hr><br>
		<!-- 3.2 Добавить кнопку Запустить. Запускает все тесты, потом выводит список тестов со статусами -->
		<form method="get">
			<input type="hidden" name="run" value="1">
			<input type="submit" value="Запустить все тесты">
		</form>

	<?php } else {/*************************************** РЕЖИМ ДЕТАЛЕЙ ТЕСТА ************************/?>

		<!-- 3.4 Можно зайти в отдельный тест. Передать можно как параметр имя класса. -->
		<h1>Тест: <?=htmlspecialchars($_GET["test"]) /* Вывести название теста */ ?></h1>
		<?php
			$test = $_GET["test"];
			/* При заходе в детальную теста проверить, что его класс есть хотя бы
			в одном из списков (prod/dev) и что класс унаследован от прототипа тестов. */
			$i = array_search($test, $Api->tests);
			if ( ($i !== false) && (strpos(get_parent_class($test),'TestPrototype') !== false) ) {
				
				if ( (!isset($_SESSION[$test."_stat"])) || ($run) ) {
					$color = Component\SiteTester\Status::getColor( $tobj[$i]->run() ); // Запустить тест
					$_SESSION[$test."_stat"] = "<span style=\"color: $color\">".$_SESSION[$test."_stat"]."</span>".' ('.date("D, d M Y H:i:s").')';
				}

				echo "<p>".$_SESSION[$test."_stat"]."</p>\r\n"; // Вывести статус с подсветкой цветом
				
				if (!empty($_SESSION[$test."_mes"])) { // Вывести список сообщений, если были найдены
					$messages = explode('#',$_SESSION[$test."_mes"]);
					echo "<br><div style=\"padding-left: 40px; font-size: 12px\">\r\n";
					foreach ($messages as $mess) {
						$text = strtok($mess,"|");
						echo "<p>";
						switch ((int)substr($text,0,1)) {
							case API::MESSAGE_TYPE_WARNING: echo 'Warning: '; break;
							case API::MESSAGE_TYPE_ERROR: echo 'Error: '; break;
							case API::MESSAGE_TYPE_INFO: echo 'Info: '; break;
						}
						echo substr($text,1)." (".strtok("|").")</p>\r\n";
					}
					echo "</div>";
				}
			?>
				<hr><br>
				<!-- Добавить кнопку "Запустить" для повторного запуска -->
				<form method="get">
					<input type="hidden" name="test" value="<?=$test?>">
					<input type="hidden" name="run" value="1">
					<input type="submit" value="Повторный запуск">
				</form>
			<?php
			} else echo ("<p>Указанный тест не найден!</p>");
		?>
		<br><a href="<?=$_SERVER['PHP_SELF']?>">Вернуться к списку тестов</a>
	<?php } ?>
</body>
</html>