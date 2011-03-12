<?php

namespace {

	if (!class_exists('NotImplementedException', FALSE)) {
		class NotImplementedException extends LogicException {}
		class NotSupportedException extends LogicException {}
		class MemberAccessException extends LogicException {}
		class InvalidStateException extends RuntimeException {}
		class IOException extends RuntimeException {}
		class FileNotFoundException extends IOException {}
	}

}

namespace Nette {

	if (!interface_exists('Nette\IDebugPanel', FALSE)) {
		interface IDebugPanel {}
	}

}