<?php
	namespace Tests;
	class rights755 extends \Tests\rights777 { // права на все директории 755 (кроме заданных) и владелец не apache пользователь
		protected $exceptions = array('sitetester', 'logs', 'files'); // Пропускаемые директории

		protected function checkDir ($dirName) {
			$dirPerms = (int)substr(sprintf('%o', fileperms($dirName)),-4);
			$dirUser = posix_getpwuid(fileowner($dirName));

			$mess = "Директория: \"$dirName\", права: $dirPerms, пользователь: ".$dirUser['name'];
			if ( ($dirPerms != 755) || ($dirUser['name'] === 'apache') ) {
				$this->addMessageError($mess);
				return false;
			} else {
				$this->addMessageInfo($mess);
				return true;
			}
		}
	}
?>