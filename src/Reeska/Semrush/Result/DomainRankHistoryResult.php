<?php
namespace Reeska\Semrush\Result;

use Reeska\Semrush\Constants;

/**
 * Domain rank history result.
 *
 * @author Reeska
 */
class DomainRankHistoryResult extends DomainRanksResult {
	/**
	 * Date of results, format: "YYYYMMDD", DD can be:
	 * - Day of month if display_daily = 1
	 * - Database otherwise.
	 * @see Constants::DT
	 * @return string
	 */
	public function actualDate() {
		return $this->get(Constants::DT);
	}	
}