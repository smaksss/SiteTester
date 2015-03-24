<?php
	namespace Tests;
	class DropTesterExecption extends \Component\SiteTester\TestPrototype { // Тест исключения TesterException
		protected function execute() { // выполнить тест
			throw new \Component\SiteTester\TesterException('Тест не выполнен.'); // выбросить TesterException
			$this->setStatusOk('Тест пройден успешно');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_PROD;
		}
	}
?>