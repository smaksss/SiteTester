<?php
	class rights777 extends Component\SiteTester\TestPrototype { // права на определенные директории 777 (log, files, ...)
		protected function execute() { // выполнить тест
			$success = true;
			$testDirs = array('logs', 'files'); // Проверяемые директории
			$arr = glob(SITE_DIR.'*',GLOB_ONLYDIR);

			while (count($arr)) {
				$dirName = array_pop($arr);
				if (!in_array(basename($dirName),$testDirs)) {
					array_splice($arr, 1024, 0, glob($dirName.'\*',GLOB_ONLYDIR));
					continue;
				}

				$dirPerms = (int)substr(sprintf('%o', fileperms($dirName)),-4);

				$mess = "Директория: \"$dirName\", права: $dirPerms";
				if ($dirPerms != 777) {
					$this->addMessageError($mess);
					$success = false;
				} else $this->addMessageInfo($mess);
			}

			if ($success) {
				$this->setStatusOk('Тест пройден успешно');
			} else $this->fail('Тест не выполнен');
		}
		public function getMode() { // отдает константу из Api
			return API::MODE_PROD;
		}
	}
?>