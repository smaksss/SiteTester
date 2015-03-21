<?php
	class basesql extends Component\SiteTester\TestPrototype { // Класс теста наличия файла live.config
		protected function execute() { // выполнить тест
			if (file_exists(SITE_DIR.'base.sql')) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { return API::MODE_PROD; } // отдает константу из Api
	}
?>