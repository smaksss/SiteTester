<?php
namespace Component\SiteTester;
	abstract class Status { // класс с набором констант
		const TYPE_OK = 1; // тест пройден (зеленый)
		const TYPE_WARNING = 2; // предупреждения (желтый)
		const TYPE_FAIL = 3; // тест провален (красный)
		const TYPE_ERROR = 4; // тест упал с ошибкой (красный)
		const TYPE_SKIP = 5; // тест пропущен (серый)
		const TYPE_MESSAGE = 6; // информационные сообщения

		static function getColor($status) { // По статусу отдаст цвет для раскраски
			switch ($status) {
				case self::TYPE_OK: return '#3cb371';
				case self::TYPE_WARNING: return '#dfbe00';
				case self::TYPE_FAIL: return '#b22222';
				case self::TYPE_ERROR: return '#b22222';
				case self::TYPE_SKIP: return '#636363';
				case self::TYPE_MESSAGE: return '#4169e1';
				default: return '#000000';
			}
		}
	}
?>