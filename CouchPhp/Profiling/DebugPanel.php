<?php

namespace CouchPhp;

use Nette\Diagnostics\IBarPanel,
	Nette\Diagnostics\Debugger,
	LogicException;



require_once __DIR__ . '/../nette.php'; // required by apigen



/**
 * Nette\Debug panel.
 */
class DebugPanel extends Object implements IProfiler, IBarPanel
{
	private $connection;

	private $log = array();

	private $totalTime = 0;



	public function __construct(Connection $connection)
	{
		if (class_exists('Nette\Diagnostics\Debugger', FALSE)) {
			Debugger::$bar->addPanel($this);
			Debugger::$blueScreen->addPanel(callback($this, 'renderException'), __CLASS__);
		} else {
			$class = get_called_class();
			throw new LogicException("$class requires Nette\\Diagnostics\\Debugger");
		}
		$this->connection = $connection;
	}



	public function logRequest(Request $request)
	{
		$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR;
		$source = NULL;
		foreach (debug_backtrace(FALSE) as $row) {
			if (isset($row['file']) && is_file($row['file']) && strpos($row['file'], $dir) !== 0) {
				$source = array($row['file'], (int) $row['line']);
				break;
			}
		}
		$this->log[] = array($request, NULL, $source);
	}



	public function logResponse(Response $response)
	{
		$this->log[count($this->log) - 1][1] = $response;
		$this->totalTime += $response->time;
	}



	public function renderException($e)
	{
		if ($e instanceof RequestException) {
			return array(
				'tab' => 'CouchPhp',
				'panel' => $this->renderExceptionPanel($e),
			);
		}
	}



	public function renderExceptionPanel($e)
	{
		$r = $e->getRequest();
		$h = function($s) { return htmlspecialchars($s, ENT_QUOTES); };
		$s = "<p><b>Request</b></p><table><tr>"
			. "<th>$r->method</th><td><a href=\"http://{$h($r->uri)}\">{$h(rawurldecode($r->uri))}</a></td></tr>"
			. "<th>Headers</th><td>";

		foreach ($r->getHeaders() as $n => $v) {
			$s .= "<b>{$h($n)}:</b> {$h($v)}<br />";
		}

		$s .= "</td></tr><tr><th>Post data</th><td>" . Debugger::dump($r->postData, TRUE) . "</td></tr></table>";

		if ($r = $e->getResponse()) {
			$s .= "<p><b>Response</b></p><table><tr>"
				. "<th>Headers</th><td>";

			foreach ($r->getHeaders() as $n => $v) {
				$s .= "<b>{$h($n)}:</b> {$h($v)}<br />";
			}

			$s .= "</td></tr><tr><th>Body</th><td>" . Debugger::dump($r->body, TRUE) . "</td></tr></table>";
		}
		return $s;
	}



	public function getId()
	{
		return __CLASS__;
	}



	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAYBQTFRF////752d6AAA0AAA8aCg6xQc6WVn8aOj/u7vzgAA8J6e5iUq5mNk1wAA5Gpq4S8x8J2d7AgN4xMX3gAA/vv7+K6x/eLk75aX5x0j4AAD5Bwh5Bwi7B4m6wAC6QIJ7hwl7QgS/v//8R8q8yMu6gwU5gAA7pmZ3QIF83Z77Bsj8I+R87W10gAAxgAA3AAA+uTk2QAA++fn8Zuc0QAA5B8k3AQG/uXm76Wl/dTX+9XV/eDh+dPU8Vpg6XBx7Q8Y6HZ30wYF8aOk6QEHywAA3wcK7BEY6g8Y3QcK/fLy/ejp/vr6/PLx7ZeX52Jj8Bok5SUq6gcP4AAA6gAF//7+7R8o5Bsg/P//7Z2d4A0N75yc3TIx/Nvc705S7ikx+Kuv+cPF+vb12gwN50hI3gcK7gcS5hsh6lpd3wAD/u3u7AcQ++nq1QAA7Bcf7pyc+d/f4wAA96Wp/fv67BEa5Roh7A0W1AAA/u/w5Tg884yP9ra47x4n7SAo63N18p+g9Wdu5l9g9It3lAAAALdJREFUeNpiYCAdGIozWNt7M0Rz1PKwsfnaejHkmYh5mlnFyKkwscpoZnInMDBUBjMwiOYz2WgEBYQo+IcxsDNwADXGWTIwFDgxFUnUM5TX8SuCzDIPZ8hISvSLYBBK0TNiYNDn42SuKctKE5RmCK1ylbVgqAhkzVF1q46Vl2JIVXLX0WKIF3ZJNjUoVVcuZpAsydaNEil0cGZmNubktUtncGQRyFXzYND2iWRkZORiYSfDbwABBgDg6R/0y5ID7wAAAABJRU5ErkJggg==" />'
			. $this->connection->getUri() . ' <b style="background: #000; opacity: 0.65; color: #fff; padding: 1px 3px; border-radius: 3px">' . count($this->log) . '</b>';
	}



	public function getPanel()
	{
		$connection = $this->connection;
		$totalTime = $this->totalTime;
		$log = $this->log;
		ob_start();
		include __DIR__ . '/DebugPanel.phtml';
		return ob_get_clean();
	}
}
