<?php
namespace Component\SiteTester;
	class TestMessage { // Класс сообщения теста
		private $MESSAGE_OF_TYPE = array('Warning', 'Error', 'Info');
		private $COLORS_OF_TYPE = array('#0000', '#ff0000', '#0000ff');
		private $MessType, $MessText, $MessDate;
		
		function __construct ($text) {
			$text = strtok($text,"|");
			$this->MessType = (int)substr($text,0,1);
			$this->MessText = substr($text,1);
			$this->MessDate = strtok('|');
		}
		
		public function getRunResultType() {
			return $this->MESSAGE_OF_TYPE[$this->MessType];
		}
		public function getResultColor() {
			return $this->COLORS_OF_TYPE[$this->MessType];
		}
		public function getMessage() {
			return $this->MessText.' ('.$this->MessDate.')';
		}
	}
?>