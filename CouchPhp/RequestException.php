<?php

namespace CouchPhp;

use RuntimeException,
	Nette\Debug,
	Nette\IDebugPanel;



require_once __DIR__ . '/nette.php'; // required by apigen



/**
 * Occurs when request cannot be properly fullfilled.
 */
class RequestException extends RuntimeException implements IDebugPanel
{
	private $request;

	private $response;



	public function __construct($message = NULL, $code = 0, Request $request, Response $response = NULL)
	{
		parent::__construct($message, $code);
		$this->request = $request;
		$this->response = $response;
	}



	public function getId()
	{
		return __CLASS__;
	}



	public function getTab()
	{
		return 'CouchPhp';
	}



	public function getPanel()
	{
		$r = $this->request;
		$h = function($s) { return htmlspecialchars($s, ENT_QUOTES); };
		$s = "<p><b>Request</b></p><table><tr>"
			. "<th>$r->method</th><td><a href=\"http://{$h($r->uri)}\">{$h(rawurldecode($r->uri))}</a></td></tr>"
			. "<th>Headers</th><td>";

		foreach ($r->getHeaders() as $n => $v) {
			$s .= "<b>{$h($n)}:</b> {$h($v)}<br />";
		}

		$s .= "</td></tr><tr><th>Post data</th><td>" . Debug::dump($r->postData, TRUE) . "</td></tr></table>";

		if ($this->response) {
			$r = $this->response;
			$s .= "<p><b>Response</b></p><table><tr>"
				. "<th>Headers</th><td>";

			foreach ($r->getHeaders() as $n => $v) {
				$s .= "<b>{$h($n)}:</b> {$h($v)}<br />";
			}

			$s .= "</td></tr><tr><th>Body</th><td>" . Debug::dump($r->body, TRUE) . "</td></tr></table>";
		}
		return $s;
	}
}
