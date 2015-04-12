<?php
namespace Reeska\Semrush\Factory;

use Reeska\Semrush\Result\DomainRanksResult;
use Reeska\Semrush\Result\DomainRankHistoryResult;

class DomainRankHistoryResultFactory implements ResultFactory {
	private static $instance = null;

	/**
	 * @return DomainRanksResultFactory
	 */
	public static function instance() {
		return self::$instance == null ?
		self::$instance = new DomainRankHistoryResultFactory():
		self::$instance;
	}

	/**
	 * Create DomainRankResult instance.
	 * @return DomainRankHistoryResult
	 */
	public function create(array $data, array $columns) {
		return new DomainRankHistoryResult($data, $columns);
	}
}