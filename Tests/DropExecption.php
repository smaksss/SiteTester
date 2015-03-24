<?php
	namespace Tests;
	class DropExecption extends \Component\SiteTester\TestPrototype { // Тест исключения \Exception
		protected function execute() { // выполнить тест
			throw new \Exception('Тест не выполнен.'); // выбросить \Exception
			$this->setStatusOk('Тест пройден успешно');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_DEV;
		}
	}
?>