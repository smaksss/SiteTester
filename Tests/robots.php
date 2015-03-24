<?php
	namespace Tests;
	class robots extends \Component\SiteTester\TestPrototype { // Класс тестирования файла robots.txt
		protected function execute() { // выполнить тест
			$search = array('aaaa', 'bbbb', 'cccc', 'dddd'); // Массив поиска
			$sum = count($search); // Счётчик совпадений

			if ( $handle = @fopen (SITE_DIR.'robots.txt','r') ) {
				$newsearch = 0;
				while (!feof($handle)) {
					$line = strtolower(trim(fgets($handle)));
					$i = array_search($line, $search);
					if ($i !== false) {
						unset($search[$i]);
						$sum--;
					}
				}
				fclose($handle);
				if (!$sum) {
					$this->setStatusOk();
				} else $this->fail('Тест не выполнен');
			} else {
				$this->addMessageError('Не удаётся открыть файл robots.txt');
				$this->setStatusError('Ошибка выполнения');
			}
		}
		public function getMode() { // отдает константу из Api
			return \API::MODE_DEV;
		}
	}
?>