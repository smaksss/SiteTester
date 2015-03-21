<?php
	class robots extends Component\SiteTester\TestPrototype { // Класс тестирования файла robots.txt
		protected function execute() { // выполнить тест
			if ( $handle = @fopen (SITE_DIR.'robots.txt','r') ) {
				$newsearch = 0;
				while (!feof($handle)) {
					$line = strtolower(trim(fgets($handle)));
					if (strpos($line, "user-agent:") === 0) $newsearch = 1;
					switch ($newsearch) {
						case 1: if ($line === "user-agent: *") $newsearch = 2; break;
						case 2: if ($line === "disallow: /") {
								  		$this->setStatusOk('Тест пройден успешно');
								  		$newsearch = 3;
								  }
								  break 2;
					}
				}
				fclose($handle);
				if (!$newsearch) $this->addMessageWarning('Не найдена директива User-agent');
				if ($newsearch != 3) $this->fail('Тест не выполнен');
			} else {
				$this->addMessageError('Не удаётся открыть файл robots.txt');
				$this->setStatusError('Ошибка выполнения');
			}
		}
		public function getMode() { return API::MODE_DEV; } // отдает константу из Api
	}
?>