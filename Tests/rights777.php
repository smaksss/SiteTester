<?php
	namespace Tests;
	class rights777 extends \Component\SiteTester\TestPrototype { // права на определенные директории 777 (log, files, ...)
		protected $testDirs = array('logs', 'files'); // Проверяемые директории

		protected function checkDir ($dirName) {
			$dirPerms = (int)substr(sprintf('%o', fileperms($dirName)),-4);
			$mess = "Директория: \"$dirName\", права: $dirPerms";
			if ($dirPerms != 777) {
				$this->addMessageError($mess);
				return false;
			} else {
				$this->addMessageInfo($mess);
				return true;
			}
		}

		protected function ScanDir ($path, array $testDirs, array $exceptions) { // Рекурсивная ф-ция сканирования директории
			static $success = true;

			$arr = glob($path, GLOB_ONLYDIR);
			foreach ($arr as $dirName) {

				if ( (!empty($testDirs)) && (!in_array(basename($dirName),$testDirs)) ) {
					$this->ScanDir ($dirName.'\*', $testDirs, $exceptions);
					continue;
				}
				
				if (in_array(basename($dirName),$exceptions)) continue;
				
				$success = $this->checkDir($dirName);
			}
			return $success;
		}
		
		protected function execute() { // выполнить тест
			if ($this->ScanDir(SITE_DIR.'*',
									(!isset($this->exceptions)) ? $this->testDirs : array(),
									(isset($this->exceptions)) ? $this->exceptions : array()
									)) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_PROD;
		}
	}
?>