<?php
	function newbasename ($path) { // Ф-ция для извлечения имени из пути с двумя типами разделителей (/ и \)
		return preg_replace("/^.*[\/\\\]/", "", $path);
	}

	class API { // основной класс для работы
		const MODE_PROD = 0;
		const MODE_DEV = 1;

		const MESSAGE_TYPE_WARNING = 0;
		const MESSAGE_TYPE_ERROR = 1;
		const MESSAGE_TYPE_INFO = 2;

		public $tests = array(); // Список найденных тестов
		public $tobj = array (); // Сисок объектов тестов

		static public function getSiteMode() { // отдает константу MODE_PROD или MODE_DEV
			if (file_exists(SITE_DIR.'testmode.ini')) {
				return self::MODE_DEV;
			} else return self::MODE_PROD;
		}
		public function SearchTests() { // Поиск тестов
			foreach (glob('Tests/*.php') as $filename) $this->tests[] = 'Tests\\'.substr(newbasename($filename),0,-4);
		}
		public function getTestList() { // TestPrototype[] - отдает набор объектов тестов
			$TestList = array();
			foreach ($this->tests as $test) $TestList[] = new $test;
			return $TestList;
		}
		public function getProdTestList() { // : string[] - отдает набор имен классов тестов для живого сайта
			/* собираться должен так
				return array(
					Test\Name123::getClass(),
					Test\Name456::getClass(),
					...
				) */
			$ProdTestList = array();
			foreach ($this->tests as $test) if (@$test::getMode() == self::MODE_PROD) $ProdTestList[] = $test;
			return $ProdTestList;
		}
		public function getDevTestList() { // : string[] - отдает набор имен классов тестов для тестового сайта
			$DevTestList = array();
			foreach ($this->tests as $test) if (@$test::getMode() == self::MODE_DEV) $DevTestList[] = $test;
			return $DevTestList;
		}
	}
?>