<?php

namespace {

	if (!defined('NETTE')) {
		class NotImplementedException extends LogicException {}
		class NotSupportedException extends LogicException {}
		class MemberAccessException extends LogicException {}
		class InvalidStateException extends RuntimeException {}
		class IOException extends RuntimeException {}
		class FileNotFoundException extends IOException {}
	}

}

namespace Nette {

	if (!defined('NETTE')) {
		interface IDebugPanel {}
	}

}