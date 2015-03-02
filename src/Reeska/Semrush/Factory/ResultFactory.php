<?php
namespace Reeska\Semrush\Factory;

use Reeska\Semrush\Result\SemrushResult;

interface ResultFactory {
	/**
	 * 
	 * @param array $data
	 * @param array $column
	 * @return SemrushResult
	 */
	public function create(array $data, array $column);
}