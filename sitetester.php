<?php
	date_default_timezone_set("Europe/Moscow");
	session_start();

	/* Создание заголовка не кэшируемой в браузерах страницы */
	header("Cache-Control: private");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	const SITE_DIR = '../'; // Путь к директории тестируемого сайта

	function __autoload($name) { // Автоматическое добавление классов
		require_once(str_replace('\\','/',$name).".php");
	}
	spl_autoload_register('__autoload'); // Регистрация ф-ции autoload

	$Api = new API; // Создать объект главного класса
	$Api->SearchTests(); // Поиск тестов
	$ProdTestList = $Api->getProdTestList(); // Список классов тестов для живого сайта
	$DevTestList = $Api->getDevTestList(); // Список классов тестов для тестового сайта

	if (isset($_GET["run"])) { // Датчик запуска тестов
		$run = true;
	} else $run = false;

	$Twig = array();

	if (!isset($_GET["test"])) { // ******************** РЕЖИМ СПИСКА ТЕСТОВ ************************
		/* 3.1 Основной интерфейс - списковый
		Выводит все тесты, помечает, что не были запущены */
		$Twig['Mode'] = 1;
		$tobj = $Api->getTestList(); // Получение списка объектов тестов
		$Twig['TestList'] = array();
		$i = 0;
		foreach ($Api->tests as $test) {
			$Twig['TestList'][$i] = array();
			if (in_array($test, $ProdTestList)) {
				$Twig['TestList'][$i]['Type'] = '[prod]';
			} else $Twig['TestList'][$i]['Type'] = '[dev]';
			$Twig['TestList'][$i]['Name'] = basename($test);

			if ($run) {
				$color = Component\SiteTester\Status::getColor( $tobj[$i]->run() ); // Выполнить тест
				/* При запуске сделать сохранение статусов в сессию.
				Тогда можно будет просто вызвать список, а он выведет статусы, которые буди вычислены. */
				$_SESSION[$test."_stat"].= '|'.$color.'|'.date("D, d M Y H:i:s");
			}

			if (isset($_SESSION[$test."_stat"])) {
				$Twig['TestList'][$i]['Stat'] = array (
																	'Text'=>strtok($_SESSION[$test."_stat"],'|'),
																	'Color'=>strtok('|'),
																	'Date'=>'('.strtok('|').')'
																	);
			} else $Twig['TestList'][$i]['Stat'] = array (
																		 'Text'=>'(не выполнялся)',
																		 'Color'=>'#000000',
																		 'Date'=>''
																		 );
			$i++;
		}

	} else { // *************************************** РЕЖИМ ДЕТАЛЕЙ ТЕСТА ************************
		// Можно зайти в отдельный тест. Передать можно как параметр имя класса.
		$Twig['Name'] = htmlspecialchars($_GET["test"]); // Вывести название теста
		$test = 'Tests\\'.$_GET["test"];

		/* При заходе в детальную теста проверить, что его класс есть хотя бы
		в одном из списков (prod/dev) и что класс унаследован от прототипа тестов. */
		if ( (in_array($test, $Api->tests)) && (basename(get_parent_class($test)) === 'TestPrototype') ) {

			if ( (!isset($_SESSION[$test."_stat"])) || ($run) ) {
				$tobj[0] = new $test;
				$color = Component\SiteTester\Status::getColor( $tobj[0]->run() ); // Запустить тест
				$_SESSION[$test."_stat"].= '|'.$color.'|'.date("D, d M Y H:i:s");
			}

			$Twig['Stat'] = array ( // Вывести статус с подсветкой цветом
											'Text'=>strtok($_SESSION[$test."_stat"],'|'),
											'Color'=>strtok('|'),
											'Date'=>'('.strtok('|').')'
											);

			if (!empty($_SESSION[$test."_mes"])) { // Вывести список сообщений, если были найдены
				$Twig['Messages'] = array();
				$messages = explode('#',$_SESSION[$test."_mes"]);
				$i = 0;
				foreach ($messages as $mess) {
					$text = strtok($mess,"|");
					switch ((int)substr($text,0,1)) {
						case API::MESSAGE_TYPE_WARNING:
							$Twig['Messages'][$i] = 'Warning: ';
							break;

						case API::MESSAGE_TYPE_ERROR:
							$Twig['Messages'][$i] = 'Error: ';
							break;

						case API::MESSAGE_TYPE_INFO:
							$Twig['Messages'][$i] = 'Info: ';
							break;
					}
					$Twig['Messages'][$i].= substr($text,1)." (".strtok("|").')';
					$i++;
				}
			}
		}
		$Twig['BackUrl'] = $_SERVER['PHP_SELF'];
	}

	require_once 'Twig/Autoloader.php';
	Twig_Autoloader::register(true);
	try {
		$loader = new Twig_Loader_Filesystem('Templates');
		$twig = new Twig_Environment($loader);
		$template = $twig->loadTemplate('interface.tmpl');
		echo $template->render($Twig);
	} catch (Exception $e) {
		die ('ERROR: ' . $e->getMessage());
	}
?>