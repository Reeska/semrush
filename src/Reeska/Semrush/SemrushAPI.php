<?php
namespace Reeska\Semrush;

class SemrushAPI {
	private static $endpoint = 'http://api.semrush.com';
	private static $options = array(
		'domain_organic' => array(
			'domain' => '',
			'database' => 'fr',
			'display_limit' => 5, 	/* 10000 */
			'display_offset' => 0,
			'export_escape' => 1, 	/* 0: protect by ", 1: no protect */
			'export_decode' => 1, 	/* 0: url encoded, 1: no encoding */
			'display_date' => '', 		/* format YYYYMM15 */
			'export_columns' => 'Ph,Po,Pp,Pd,Nq,Cp,Ur,Tr,Tc,Co,Nr,Td', /* Ph, Po, Pp, Pd, Nq, Cp, Ur, Tr, Tc, Co, Nr, Td */
			'display_sort' => '', 		/* sort by tr_asc, tr_desc, po_asc, po_desc, tc_asc, tc_desc */
			'display_positions' => '', 	/* new, lost, rise ou fall */
			'display_filter' => ''		/* <sign>|<field>|<operation>|<value> */
		),
		'domain_ranks' => array(
			'domain' => '',
			'database' => 'fr',
			'display_date' => '',
			'export_columns' => 'Db,Dn,Rk,Or,Ot,Oc,Ad,At,Ac'
		)
	);
	
	/**
	 * The keyword bringing users to the website via Google's top 20 organic search results.
	 */
	const PH = 'Ph';
	/**
	 * The position the URL gets in organic search for the given keyword at the specified period.
	 */
	const PO = 'Po';  
	/**
	 * The site's position for the search query (at the time of prior data collection)
	 */
	const PP = 'Pp';  
	/**
	 * The difference between keyword position in previous month and keyword position in current month
	 */
	const PD = 'Pd';  
	/**
	 * The average number of search queries for the given keyword for the last 12 months.
	 */
	const NQ = 'Nq';  
	/**
	 * Average price in U.S. dollars advertisers pay for a user’s click on an ad containing the given keyword (Google AdWords).
	 */
	const CP = 'Cp';  
	/**
	 * Url of the target page
	 */
	const UR = 'Ur';  
	/**
	 * The share of traffic driven to the website with the given keyword for the specified period.
	 */
	const TR = 'Tr';  
	/**
	 * Estimated price of the given keyword in Google AdWords.
	 */
	const TC = 'Tc';  
	/**
	 * Competitive density of advertisers using the given term for their ads. One (1) means the highest competition.
	 */
	const CO = 'Co';  
	/**
	 * The number of URLs displayed in organic search results for the given keyword.
	 */
	const NR = 'Nr';  
	/**
	 * The interest of searchers in the given keyword during the period of 12 months. The metric is based on changes in the number of queries per month.
	 */
	const TD = 'Td';  
	/**
	 * Database
	 */
	const DB = 'Db';
	/**
	 * The website ranking in Google's top 20 organic search results. Click the sign with a small arrow to view the website, or click the link to open the Overview Report for the domain.
	 */
	const DN = 'Dn';
	/**
	 * The SEMrush rating of the websites’s popularity based on organic traffic coming from Google's top 20 organic search results.
	 */
	const RK = 'Rk';		
	/**
	 * Keywords bringing users to the website via Google's top 20 organic search results.
	 */
	const ORG = 'Or';
	/**
	 * Traffic brought to the website via Google's top 20 organic search results.
	 */
	const OT = 'Ot';
	/**
	 * Estimated price of organic keywords in Google AdWords.
	 */
	const OC = 'Oc';
	/**
	 * Keywords the website is buying in Google AdWords for ads that appear in paid search results.
	 */
	const AD = 'Ad';
	/**
	 * Traffic brought to the website via Google AdWords paid search results.
	 */
	const AT = 'At';
	/**
	 * Estimated budget spent buying keywords in Google AdWords for ads that appear in paid search results (monthly estimation).
	 */
	const AC = 'Ac';
	
	private $key;
	
	public function __construct($key) {
		$this->key = $key;
	}

	/**
	 * Do an domain organic search keywords.
	 * @param string|array $params Domain or multiple params.
	 * @return DomainOrganicResult[]
	 */
	public function organicSearchKeywords($params) {
		if (!is_array($params)) {
			$params = array('domain' => $params);
		}
		
		return $this->request('domain_organic', $params, DomainOrganicResultFactory::instance());
	}
	
	/**
	 * Do a domain ranks query.
	 * @param string|array $params Domain or multiple params.
	 * @return DomainRankResult[]
	 */
	public function domainRanks($params) {
		if (!is_array($params)) {
			$params = array('domain' => $params);
		}

		return $this->request('domain_ranks', $params, DomainRanksResultFactory::instance());
	}
	
	/**
	 * Build params for request.
	 * @param array $params Params to merge with default options.
	 * 
	 * @return string
	 */
	protected function build($type, &$params)  {
		$defaults = self::$options[$type];
		$params = array_merge(
			array('type' => $type),
			array('key' => $this->key), 
			$defaults, 
			$params
		);
		
		/*
		 * remove empty params
		 */
		$params = array_diff($params, array(''));
		
		return http_build_query($params);
	}
	
	/**
	 * Send a request to Semrush API with this params.
	 * @param array $params
	 * @return multitype:SemrushResult |boolean
	 */
	protected function request($type, $params, ResultFactory $factory) {
		$url = self::$endpoint .'/?'. $this->build($type, $params);
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
		
		if (isset($_SERVER['SERVER_ADDR'])){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Real-IP', $_SERVER['SERVER_ADDR']));
		}
		
		$answer	= curl_exec($ch);
		
		$hreturn = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errcode = curl_errno($ch);
		
		/**
		 * Success
		 */
		if ($hreturn == 200) {
			return $this->parse($answer, $params, $factory);
		}
		
		throw new \Exception($answer, $hreturn);

		/**
		 * Error
		 */
		return false;		
	}
	
	/**
	 * Parse Semrush CSV response to SemrushResult class.
	 * @param string $response Request response.
	 * @param array $params Used params for request.
	 * @return multitype:SemrushResult
	 */
	protected function parse($response, $params, ResultFactory $factory) {
		$result = array();
		$columns = explode(',', $params['export_columns']);
		$lines = explode("\n", $response);
		
		unset($lines[0]); // headers
		
		foreach($lines as $line) {
			$result[] = $factory->create(str_getcsv($line, ";"), $columns);
		}
		
		return $result;
	}
}

/**
 * Generic semrush request's response.
 * 
 * @author Reeska
 */
class SemrushResult {
	private $data;
	private $columns;
	private $rcolumns;
	
	/**
	 * 
	 * @param array $data
	 * @param array $columns
	 */
	public function __construct($data, $columns) {
		$this->data = $data;
		$this->columns = $columns;
		$this->rcolumns = array_flip($columns);
	}
	
	/**
	 * Get raw array data.
	 * @return array
	 */
	public function data() {
		return $this->data;
	}
	
	/**
	 * Get specific column data.
	 * @param string $column
	 * @throws Exception
	 * @return mixed
	 */
	public function get($column) {
		if (!in_array($column, $this->columns)) {
			throw new \Exception("No column ". $column ." in this result.");
			return null;
		}
		
		return $this->data[$this->rcolumns[$column]];
	}
}

/**
 * Domain organic result.
 * 
 * @author Reeska
 */
class DomainOrganicResult extends SemrushResult {
	/**
	 * The keyword bringing users to the website via Google's top 20 organic search results.
	 * @see SemrushApi::PH
	 * @return string
	 */
	public function keyword() {
		return $this->get(SemrushApi::PH);
	}
	
	/**
	 * The position the URL gets in organic search for the given keyword at the specified period.
	 * @see SemrushApi::PO
	 * @return int
	 */
	public function position() {
		return $this->get(SemrushApi::PO);
	}	
	
	/**
	 * The site's position for the search query (at the time of prior data collection)
	 * @see SemrushApi::PP
	 * @return int
	 */
	public function previousPosition() {
		return $this->get(SemrushApi::PP);
	}
	
	/**
	 * The difference between keyword position in previous month and keyword position in current month
	 * @see SemrushApi::PD
	 * @return int
	 */	
	public function positionDifference() {
		return $this->get(SemrushApi::PD);
	}	
	
	/**
	 * The average number of search queries for the given keyword for the last 12 months.
	 * @see SemrushApi::NQ
	 * @return int
	 */	
	public function searchVolume() {
		return $this->get(SemrushApi::NQ);
	}
	
	/**
	 * Average price in U.S. dollars advertisers pay for a user’s click on an ad containing the given keyword (Google AdWords).
	 * @see SemrushApi::CP
	 * @return float
	 */	
	public function averageCPC() {
		return $this->get(SemrushApi::CP);
	}
	
	/**
	 * Url of the target page
	 * @see SemrushApi::UR
	 * @return string
	 */	
	public function url() {
		return $this->get(SemrushApi::UR);
	}	
	
	/**
	 * The share of traffic driven to the website with the given keyword for the specified period.
	 * @see SemrushApi::TR
	 * @return float
	 */	
	public function traffic() {
		return $this->get(SemrushApi::TR);
	}

	/**
	 * Estimated price of the given keyword in Google AdWords.
	 * @see SemrushApi::TC
	 * @return int
	 */	
	public function trafficCost() {
		return $this->get(SemrushApi::TC);
	}	
	
	/**
	 * Competitive density of advertisers using the given term for their ads. One (1) means the highest competition.
	 * @see SemrushApi::CO
	 * @return int
	 */	
	public function competition() {
		return $this->get(SemrushApi::CO);
	}
	
	/**
	 * The number of URLs displayed in organic search results for the given keyword.
	 * @see SemrushApi::NR
	 * @return int
	 */	
	public function numberOfResults() {
		return $this->get(SemrushApi::NR);
	}
	
	/**
	 * The difference between keyword position in previous month and keyword position in current month
	 * @see SemrushApi::PD
	 * @return string comma separated float
	 */	
	public function trends() {
		return $this->get(SemrushApi::TD);
	}	
}

/**
 * Domain rank result.
 * 
 * @author Reeska
 */
class DomainRankResult extends SemrushResult {
	/**
	 * Database.
	 * @see SemrushApi::DB
	 * @return string
	 */
	public function database() {
		return $this->get(SemrushApi::DB);
	}
	
	/**
	 * The website ranking in Google's top 20 organic search results. Click the sign with a small arrow to view the website, or click the link to open the Overview Report for the domain..
	 * @see SemrushApi::DN
	 * @return string
	 */
	public function domain() {
		return $this->get(SemrushApi::DN);
	}	
	
	/**
	 * The SEMrush rating of the websites’s popularity based on organic traffic coming from Google's top 20 organic search results.
	 * @see SemrushApi::RK
	 * @return string
	 */
	public function rank() {
		return $this->get(SemrushApi::RK);
	}
	
	/**
	 * Keywords bringing users to the website via Google's top 20 organic search results.
	 * @see SemrushApi::ORG
	 * @return string
	 */
	public function organicKeywords() {
		return $this->get(SemrushApi::ORG);
	}
	
	/**
	 * Traffic brought to the website via Google's top 20 organic search results.
	 * @see SemrushApi::OT
	 * @return string
	 */
	public function organicTraffic() {
		return $this->get(SemrushApi::OT);
	}

	/**
	 * Estimated price of organic keywords in Google AdWords.
	 * @see SemrushApi::OC
	 * @return string
	 */
	public function organicCost() {
		return $this->get(SemrushApi::OC);
	}

	/**
	 * Keywords the website is buying in Google AdWords for ads that appear in paid search results.
	 * @see SemrushApi::AD
	 * @return string
	 */
	public function adwordsKeywords() {
		return $this->get(SemrushApi::AD);
	}
	
	/**
	 * Traffic brought to the website via Google AdWords paid search results.
	 * @see SemrushApi::AT
	 * @return string
	 */
	public function adwordsTraffic() {
		return $this->get(SemrushApi::AT);
	}
	
	/**
	 * Estimated budget spent buying keywords in Google AdWords for ads that appear in paid search results (monthly estimation).
	 * @see SemrushApi::AC
	 * @return string
	 */
	public function adwordsCost() {
		return $this->get(SemrushApi::AC);
	}	
}

interface ResultFactory {
	public function create(array $data, array $column);
}

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
	 * @return DomainRankResult
	 */
	public function create(array $data, array $columns) {
		return new DomainRankResult($data, $columns);
	}
}