<?php
	namespace Tests;
	class liveconfig extends \Component\SiteTester\TestPrototype { // Класс теста наличия файла live.config
		protected function execute() { // выполнить тест
			if (file_exists(SITE_DIR.'live.config')) {
				$this->setStatusOk();
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_DEV;
		}
	}
?>