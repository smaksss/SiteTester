<?php
	namespace Tests;
	class rights755 extends \Component\SiteTester\TestPrototype { // права на все директории 755 (кроме заданных) и владелец не apache пользователь
		protected function execute() { // выполнить тест
			$success = true;
			$exceptions = array('sitetester', 'logs', 'files'); // Пропускаемые директории
			$arr = glob(SITE_DIR.'*',GLOB_ONLYDIR);

			while (count($arr)) {
				$dirName = array_pop($arr);
				if (in_array(basename($dirName),$exceptions)) continue;

				$dirPerms = (int)substr(sprintf('%o', fileperms($dirName)),-4);
				$dirUser = posix_getpwuid(fileowner($dirName));

				$mess = "Директория: \"$dirName\", права: $dirPerms, пользователь: ".$dirUser['name'];
				if ( ($dirPerms != 755) || ($dirUser['name'] === 'apache') ) {
					$this->addMessageError($mess);
					$success = false;
				} else $this->addMessageInfo($mess);

				array_splice($arr, 1024, 0, glob($dirName.'\*',GLOB_ONLYDIR));
			}

			if ($success) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_PROD;
		}
	}
?>