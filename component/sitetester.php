<?php
namespace Component\SiteTester;
	abstract class Status { // класс с набором констант
		const TYPE_OK=0; // тест пройден (зеленый)
		const TYPE_WARNING=1; // предупреждения (желтый)
		const TYPE_FAIL=2; // тест провален (красный)
		const TYPE_ERROR=3; // тест упал с ошибкой (красный)
		const TYPE_SKIP=4; // тест пропущен (серый)
		const TYPE_MESSAGE=5; // информационные сообщения

		static function getColor($status) { // По статусу отдаст цвет для раскраски
			switch ($status) {
				case self::TYPE_OK: return '#3cb371';
				case self::TYPE_WARNING: return '#dfbe00';
				case self::TYPE_FAIL: return '#b22222';
				case self::TYPE_ERROR: return '#b22222';
				case self::TYPE_SKIP: return '#636363';
				case self::TYPE_MESSAGE: return '#4169e1';
			}
		}
	}

	class TesterException extends \Exception {} // Собственный класс исключений

	abstract class TestPrototype { // прототип всех тестов
		protected $status; // текущий статус теста
		protected $messages = array(); // массив сообщений каждое сообщение состоит из текста сообщения и даты

		function __construct ($siteMode='') {}
		abstract protected function execute(); // выполнить тест
		final public function run() { // отдает константу класса Status
			if (\API::getSiteMode() != $this->getMode()) {
				$this->setStatusSkip('Тест пропущен');
				$this->addMessageInfo('Тест предназначен для другого режима сайта');
			} else {
				/* Перед запуском сбросить статус */
				$_SESSION[get_class($this)."_mes"]='';
				$_SESSION[get_class($this)."_stat"]='';

				try { // Обработка исключений
					$this->execute(); // запускает метод execute
				}
				catch (TesterException $e) {} // Перехватывает TesterException -> fail
				catch (\Exception $e) {} // Перехватывает \Exception -> error
			}

			/* Если после выполнения не поменялся статус - выдать предупреждение "Статус не установлен" */
			if (empty($this->status)) $this->setStatusMessage('Статус не установлен');

			/* результат сохраняет в сессию, чтобы потом можно было восстановить */
			$_SESSION[get_class($this)."_mes"]=join('#',$this->messages);
			$_SESSION[get_class($this)."_stat"]=$this->status.' ('.date("D, d M Y H:i:s").')';
		}
		abstract public function getMode(); // отдает константу из Api
		public function addMessage ($text, $type = \API::MESSAGE_TYPE_INFO) {
			$this->messages[]="$type$text|".date("D, d M Y H:i:s");
		}
		public function addMessageInfo ($text) {
			$this->messages[]=\API::MESSAGE_TYPE_INFO."$text|".date("D, d M Y H:i:s");
		}
		public function addMessageError ($text) {
			$this->messages[]=\API::MESSAGE_TYPE_ERROR."$text|".date("D, d M Y H:i:s");
		}
		public function addMessageWarning ($text) {
			$this->messages[]=\API::MESSAGE_TYPE_WARNING."$text|".date("D, d M Y H:i:s");
		}
		public function setStatusOk ($text = '') { // установить статус и записать сообщение, если нужно
			$text=(empty($text)) ? 'Тест пройден успешно' : $text;
			$color=Status::getColor(Status::TYPE_OK);
			$this->status="<span style=\"color: $color\">$text</span>";
		}
		/* установка всех остальных статусов c обязательным сообщением */
		public function setStatusWarning ($text) {
			$color=Status::getColor(Status::TYPE_WARNING);
			$this->status="<span style=\"color: $color\">$text</span>";
		}
		public function setStatusError ($text) {
			$color=Status::getColor(Status::TYPE_ERROR);
			$this->status="<span style=\"color: $color\">$text</span>";
			// throw new \Exception('Ошибка выполнения.');
		}
		public function setStatusSkip ($text) {
			$color=Status::getColor(Status::TYPE_SKIP);
			$this->status="<span style=\"color: $color\">$text</span>";
		}
		public function setStatusMessage ($text) {
			$color=Status::getColor(Status::TYPE_MESSAGE);
			$this->status="<span style=\"color: $color\">$text</span>";
		}
		public function fail ($text) { // прервать выполнение теста
			$color=Status::getColor(Status::TYPE_FAIL);
			$this->status="<span style=\"color: $color\">$text</span>";
			throw new TesterException('Тест не выполнен.'); // выбросить TesterException
		}
	}
?>