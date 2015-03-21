<?php
	class liveconfig extends Component\SiteTester\TestPrototype { // Класс теста наличия файла live.config
		protected function execute() { // выполнить тест
			if (file_exists(SITE_DIR.'live.config')) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { return API::MODE_DEV; } // отдает константу из Api
	}
?>