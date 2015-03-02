<?php
namespace Reeska\Semrush\Result;

use Reeska\Semrush\Constants;

/**
 * Domain organic result.
 *
 * @author Reeska
 */
class DomainOrganicResult extends SemrushResult {
	/**
	 * The keyword bringing users to the website via Google's top 20 organic search results.
	 * @see Constants::PH
	 * @return string
	 */
	public function keyword() {
		return $this->get(Constants::PH);
	}

	/**
	 * The position the URL gets in organic search for the given keyword at the specified period.
	 * @see Constants::PO
	 * @return int
	 */
	public function position() {
		return $this->get(Constants::PO);
	}

	/**
	 * The site's position for the search query (at the time of prior data collection)
	 * @see Constants::PP
	 * @return int
	 */
	public function previousPosition() {
		return $this->get(Constants::PP);
	}

	/**
	 * The difference between keyword position in previous month and keyword position in current month
	 * @see Constants::PD
	 * @return int
	 */
	public function positionDifference() {
		return $this->get(Constants::PD);
	}

	/**
	 * The average number of search queries for the given keyword for the last 12 months.
	 * @see Constants::NQ
	 * @return int
	 */
	public function searchVolume() {
		return $this->get(Constants::NQ);
	}

	/**
	 * Average price in U.S. dollars advertisers pay for a user’s click on an ad containing the given keyword (Google AdWords).
	 * @see Constants::CP
	 * @return float
	 */
	public function averageCPC() {
		return $this->get(Constants::CP);
	}

	/**
	 * Url of the target page
	 * @see Constants::UR
	 * @return string
	 */
	public function url() {
		return $this->get(Constants::UR);
	}

	/**
	 * The share of traffic driven to the website with the given keyword for the specified period.
	 * @see Constants::TR
	 * @return float
	 */
	public function traffic() {
		return $this->get(Constants::TR);
	}

	/**
	 * Estimated price of the given keyword in Google AdWords.
	 * @see Constants::TC
	 * @return int
	 */
	public function trafficCost() {
		return $this->get(Constants::TC);
	}

	/**
	 * Competitive density of advertisers using the given term for their ads. One (1) means the highest competition.
	 * @see Constants::CO
	 * @return int
	 */
	public function competition() {
		return $this->get(Constants::CO);
	}

	/**
	 * The number of URLs displayed in organic search results for the given keyword.
	 * @see Constants::NR
	 * @return int
	 */
	public function numberOfResults() {
		return $this->get(Constants::NR);
	}

	/**
	 * The difference between keyword position in previous month and keyword position in current month
	 * @see Constants::PD
	 * @return string comma separated float
	 */
	public function trends() {
		return $this->get(Constants::TD);
	}
}