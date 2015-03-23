<?php
	namespace Tests;
	class basesql extends \Component\SiteTester\TestPrototype { // Класс теста отсутствия файла base.sql в корне проекта
		protected function execute() { // выполнить тест
			if (file_exists(SITE_DIR.'base.sql')) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_PROD;
		}
	}
?>