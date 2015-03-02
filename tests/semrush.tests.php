<?php
require_once __DIR__.'/../vendor/autoload.php';

use Reeska\Semrush\SemrushAPI;

$semrush = new SemrushAPI('api_key');

try {
	$results = $semrush->organicSearchKeywords('github.com');

	foreach($results as $result) {
		var_dump($result->keyword());
	}
} catch(\Exception $e) {
	var_dump($e);
}