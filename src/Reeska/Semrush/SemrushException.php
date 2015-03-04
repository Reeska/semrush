<?php
namespace Reeska\Semrush;

class SemrushException extends \Exception {
	private $url;
	
	public function __construct($code, $message, $url) {
		parent::__construct($message, $code);
		$this->url = $url;
	}
	
	public function getURL() {
		return $this->url;
	}
}