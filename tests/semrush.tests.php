<?php
require_once __DIR__.'/../vendor/autoload.php';

use Reeska\Semrush\SemrushAPI;

$semrush = new SemrushAPI('f2a1f52b032f1a3dba3067900cfcbdd6');

try {
	$results = $semrush->organicSearchKeywords('github.com');

	foreach($results as $result) {
		var_dump($result->keyword());
	}
} catch(\Exception $e) {
	var_dump($e);
}