<?php
namespace Reeska\Semrush\Factory;

use Reeska\Semrush\Result\DomainRanksResult;

class DomainRanksResultFactory implements ResultFactory {
	private static $instance = null;

	/**
	 * @return DomainRanksResultFactory
	 */
	public static function instance() {
		return self::$instance == null ?
		self::$instance = new DomainRanksResultFactory():
		self::$instance;
	}

	/**
	 * Create DomainRankResult instance.
	 * @return DomainRanksResult
	 */
	public function create(array $data, array $columns) {
		return new DomainRanksResult($data, $columns);
	}
}