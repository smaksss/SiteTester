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
				catch (TesterException $e) { // Перехватывает TesterException -> fail
					$this->addMessageError ('Выброшено исключение TesterException');
				}
				catch (\Exception $e) { // Перехватывает \Exception -> error
					$this->addMessageError ('Выброшено исключение \Exception');
				}
			}

			/* Если после выполнения не поменялся статус - выдать предупреждение "Статус не установлен" */
			if (empty($this->status)) $this->setStatusMessage('Статус не установлен');

			$this->setMessSES ($this->messages); // результат сохраняет в сессию, чтобы потом можно было восстановить
			
			return $this->getStatusSES(); // отдает константу класса Status
		}
		abstract public function getMode(); // отдает константу из Api

		public function addMessage ($text, $type = \API::MESSAGE_TYPE_INFO) {
			$this->messages[] = array ( 'type'=>$type, 'text'=>$text, 'date'=>date("D, d M Y H:i:s") );
		}
		public function addMessageInfo ($text) {
			$this->addMessage($text);
		}
		public function addMessageError ($text) {
			$this->addMessage($text, \API::MESSAGE_TYPE_ERROR);
		}
		public function addMessageWarning ($text) {
			$this->addMessage($text, \API::MESSAGE_TYPE_WARNING);
		}

		public function setStatus ($text, $type = Status::TYPE_OK) { // установить статус
			$this->status = $type;
			$this->setStatusSES (array($type, $text));
		}
		public function setStatusOk ($text = '') { // установить статус и записать сообщение, если нужно
			$text = (empty($text)) ? 'Тест пройден успешно' : $text;
			$this->setStatus($text);
		}
		/* установка всех остальных статусов c обязательным сообщением */
		public function setStatusWarning ($text) {
			$this->setStatus($text, Status::TYPE_WARNING);
		}
		public function setStatusError ($text) {
			$this->setStatus($text, Status::TYPE_ERROR);
		}
		public function setStatusSkip ($text) {
			$this->setStatus($text, Status::TYPE_SKIP);
		}
		public function setStatusMessage ($text) {
			$this->setStatus($text, Status::TYPE_MESSAGE);
		}
		public function fail ($text) { // прервать выполнение теста
			$this->setStatus($text, Status::TYPE_FAIL);
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