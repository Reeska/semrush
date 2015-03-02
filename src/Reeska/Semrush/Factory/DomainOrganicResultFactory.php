<?php
namespace Reeska\Semrush\Factory;

use Reeska\Semrush\Result\DomainOrganicResult;

class DomainOrganicResultFactory implements ResultFactory {
	private static $instance = null;

	/**
	 * @return DomainOrganicResultFactory
	 */
	public static function instance() {
		return self::$instance == null ?
		self::$instance = new DomainOrganicResultFactory():
		self::$instance;
	}

	/**
	 * Create DomainOrganicResult instance.
	 * @return DomainOrganicResult
	 */
	public function create(array $data, array $columns) {
		return new DomainOrganicResult($data, $columns);
	}
}