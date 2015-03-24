<?php
namespace Component\SiteTester;
	class TesterException extends \Exception { } // Собственный класс исключений

	abstract class TestPrototype { // прототип всех тестов
		protected $status; // текущий статус теста
		protected $messages = array(); // массив сообщений каждое сообщение состоит из текста сообщения и даты

		function __construct ($siteMode = '') {}
		abstract protected function execute(); // выполнить тест
		final public function run ($check_mode = true) {
			if ( ($check_mode) && (\API::getSiteMode() != $this->getMode()) ) {
				$this->setStatusSkip('Тест пропущен');
				$this->addMessageInfo('Тест предназначен для другого режима сайта');
			} else {
				$this->clearSES(); // Перед запуском сбросить статус

				try { // Обработка исключений
					$this->execute(); // запускает метод execute
				}
				catch (TesterException $e) {} // Перехватывает TesterException -> fail
				catch (\Exception $e) {} // Перехватывает \Exception -> error
			}

			/* Если после выполнения не поменялся статус - выдать предупреждение "Статус не установлен" */
			if (empty($this->status)) $this->setStatusMessage('Статус не установлен');

			$this->setMessSES ($this->messages); // результат сохраняет в сессию, чтобы потом можно было восстановить
			
			return $this->getStatusSES(); // отдает константу класса Status
		}
		abstract public function getMode(); // отдает константу из Api
		public function addMessage ($text, $type = \API::MESSAGE_TYPE_INFO) {
			$this->messages[] = "$type$text|".date("D, d M Y H:i:s");
		}
		public function addMessageInfo ($text) {
			$this->messages[] = \API::MESSAGE_TYPE_INFO."$text|".date("D, d M Y H:i:s");
		}
		public function addMessageError ($text) {
			$this->messages[] = \API::MESSAGE_TYPE_ERROR."$text|".date("D, d M Y H:i:s");
		}
		public function addMessageWarning ($text) {
			$this->messages[] = \API::MESSAGE_TYPE_WARNING."$text|".date("D, d M Y H:i:s");
		}
		public function setStatusOk ($text = '') { // установить статус и записать сообщение, если нужно
			$text = (empty($text)) ? 'Тест пройден успешно' : $text;
			$this->status = Status::TYPE_OK;
			$this->setStatusSES (array($this->status, $text));
		}
		/* установка всех остальных статусов c обязательным сообщением */
		public function setStatusWarning ($text) {
			$this->status = Status::TYPE_WARNING;
			$this->setStatusSES (array($this->status, $text));
		}
		public function setStatusError ($text) {
			$this->status = Status::TYPE_ERROR;
			$this->setStatusSES (array($this->status, $text));
			// throw new \Exception('Ошибка выполнения.');
		}
		public function setStatusSkip ($text) {
			$this->status = Status::TYPE_SKIP;
			$this->setStatusSES (array($this->status, $text));
		}
		public function setStatusMessage ($text) {
			$this->status = Status::TYPE_MESSAGE;
			$this->setStatusSES (array($this->status, $text));
		}
		public function fail ($text) { // прервать выполнение теста
			$this->status = Status::TYPE_FAIL;
			$this->setStatusSES (array($this->status, $text));
			throw new TesterException('Тест не выполнен.'); // выбросить TesterException
		}

		/* Ф-ции для работы с сессией */
		public function clearSES() {
			$_SESSION[get_class($this)."_mes"] = '';
			$_SESSION[get_class($this)."_stat"] = '';
		}
		public function setStatusSES (array $arr) {
			$_SESSION[get_class($this)."_stat"] = array (
																		'status'=>$arr[0],
																		'text'=>$arr[1],
																		'date'=>date("D, d M Y H:i:s")
																		);
		}
		public function setMessSES (array $arr) {
			$_SESSION[get_class($this)."_mes"] = $arr;
		}
		public function getStatusSES () {
			return $_SESSION[get_class($this)."_stat"];
		}
		public function getMessSES () {
			return $_SESSION[get_class($this)."_mes"];
		}
	}
?>