<?php
namespace Reeska\Semrush\Result;

use Reeska\Semrush\Constants;

/**
 * Domain rank result.
 *
 * @author Reeska
 */
class DomainRanksResult extends SemrushResult {
	/**
	 * Database.
	 * @see Constants::DB
	 * @return string
	 */
	public function database() {
		return $this->get(Constants::DB);
	}

	/**
	 * The website ranking in Google's top 20 organic search results. Click the sign with a small arrow to view the website, or click the link to open the Overview Report for the domain..
	 * @see Constants::DN
	 * @return string
	 */
	public function domain() {
		return $this->get(Constants::DN);
	}

	/**
	 * The SEMrush rating of the websites’s popularity based on organic traffic coming from Google's top 20 organic search results.
	 * @see Constants::RK
	 * @return string
	 */
	public function rank() {
		return $this->get(Constants::RK);
	}

	/**
	 * Keywords bringing users to the website via Google's top 20 organic search results.
	 * @see Constants::ORG
	 * @return string
	 */
	public function organicKeywords() {
		return $this->get(Constants::ORG);
	}

	/**
	 * Traffic brought to the website via Google's top 20 organic search results.
	 * @see Constants::OT
	 * @return string
	 */
	public function organicTraffic() {
		return $this->get(Constants::OT);
	}

	/**
	 * Estimated price of organic keywords in Google AdWords.
	 * @see Constants::OC
	 * @return string
	 */
	public function organicCost() {
		return $this->get(Constants::OC);
	}

	/**
	 * Keywords the website is buying in Google AdWords for ads that appear in paid search results.
	 * @see Constants::AD
	 * @return string
	 */
	public function adwordsKeywords() {
		return $this->get(Constants::AD);
	}

	/**
	 * Traffic brought to the website via Google AdWords paid search results.
	 * @see Constants::AT
	 * @return string
	 */
	public function adwordsTraffic() {
		return $this->get(Constants::AT);
	}

	/**
	 * Estimated budget spent buying keywords in Google AdWords for ads that appear in paid search results (monthly estimation).
	 * @see Constants::AC
	 * @return string
	 */
	public function adwordsCost() {
		return $this->get(Constants::AC);
	}
}