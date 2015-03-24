<?php
namespace Component\SiteTester;
	class TestMessage { // Класс сообщения теста
		private $MESSAGE_OF_TYPE = array('Warning', 'Error', 'Info');
		private $COLORS_OF_TYPE = array('#0000', '#ff0000', '#0000ff');
		private $Mess = array();
		
		function __construct (array $mess) {
			$this->Mess = $mess;
		}
		
		public function getRunResultType() {
			return $this->MESSAGE_OF_TYPE[$this->Mess['type']];
		}
		public function getResultColor() {
			return $this->COLORS_OF_TYPE[$this->Mess['type']];
		}
		public function getMessage() {
			return $this->Mess['text'].' ('.$this->Mess['date'].')';
		}
	}
?>