<?php

namespace Nette {

	if (!class_exists(__NAMESPACE__ . '\NotImplementedException', FALSE)) {
		class NotImplementedException extends \LogicException {}
		class NotSupportedException extends \LogicException {}
		class MemberAccessException extends \LogicException {}
		class InvalidStateException extends \RuntimeException {}
		class IOException extends \RuntimeException {}
		class FileNotFoundException extends IOException {}
	}

}

namespace Nette\Diagnostics {

	if (!interface_exists(__NAMESPACE__ . '\IBarPanel', FALSE)) {
		interface IBarPanel {}
	}

}