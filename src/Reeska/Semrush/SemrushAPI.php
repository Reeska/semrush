<?php
namespace Reeska\Semrush;

use Reeska\Semrush\Factory\ResultFactory;
use Reeska\Semrush\Factory\DomainOrganicResultFactory;
use Reeska\Semrush\Factory\DomainRanksResultFactory;
use Reeska\Semrush\Result\DomainRanksResult;
use Reeska\Semrush\Result\DomainOrganicResult;
use Reeska\Semrush\Factory\DomainRankHistoryResultFactory;

/**
 * Semrush Service class to request Semrush API.
 * Usage: 
 * $semrush = new Semrush('api_key');
 * $results = $semrush->organicSearchKeywords('domain.tld');
 * 
 * @author Reeska
 */
class SemrushAPI {
	protected static $endpoint = 'https://api.semrush.com';
	protected static $domain = 'https://fr.semrush.com';

	/* target server, because www.semrush.com redirect to and 
	 * Location header doesn't contains protocol (Location://fr.semrush.com/users/countapiunits.html?key=) 
	 * so curl follow redirect doesn't works. */
	protected static $options = array(
		'domain_organic' => array(
			'domain' => '',
			'database' => 'fr',
			'display_limit' => 10000, 	/* default: 10000 */
			'display_offset' => 0,
			'export_escape' => 1, 		/* 0: no protect, 1: protect by " */
			'export_decode' => 1, 		/* 0: url encoded, 1: no encoding */
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
			'export_columns' => 'Db,Dn,Rk,Or,Ot,Oc,Ad,At,Ac',
			'export_escape' => 1 	/* 0: no protect, 1: protect by " */
		),
		'domain_rank_history' => array(
			'domain' => '',
			'database' => 'fr',
			'display_date' => '',
			'export_columns' => 'Rk,Or,Ot,Oc,Ad,At,Ac,Dt',
			'export_escape' => 1 	/* 0: no protect, 1: protect by " */
		)
	);
	
	private $timeout = 60;
	protected $debug = false;
	private $key;
	
	public function __construct($key) {
		$this->key = $key;
	}
	
	/**
	 * Enable or disable showing debug information.
	 * @param boolean $debug
	 * @return SemrushAPI
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
		return $this;
	}

	/**
	 * Set HTTP request timeout (default: 60s)
	 * @param int $timeout timeout in seconds
	 * @return SemrushAPI
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
		return $this;
	}
	
	/**
	 * Change some default options.
	 * 
	 * @param string $type Service name.
	 * @param array $params Options to override.
	 */
	public static function configure($type, $params) {
		self::$options = array_merge(self::$options, $params);
	}

	/**
	 * Do an domain organic search keywords.
	 * @param string|array $params Domain or multiple params.
	 * @param string $date Date to filter results, format: YYYYMM15 (display_date option).
	 * @param int $maxlength Max result count (display_limit option).
	 * @return DomainOrganicResult[]
	 */
	public function organicSearchKeywords($params, $date = '', $maxlength = '') {
		return $this->dispatch('domain_organic', DomainOrganicResultFactory::instance(), $params, $date, $maxlength);
	}
	
	/**
	 * Do a domain ranks query.
	 * @param string|array $params Domain or multiple params.
 	 * @param string $date Date to filter results, format: YYYYMM15 (display_date option).
	 * @param int $maxlength Max result count (display_limit option).
	 * @return DomainRanksResult[]
	 */
	public function domainRanks($params, $date = '', $maxlength = '') {
		return $this->dispatch('domain_ranks', DomainRanksResultFactory::instance(), $params, $date, $maxlength);
	}
	
	/**
	 * Do a domain rank history query.
	 * @param string|array $params Domain or multiple params.
	 * @param int $maxlength Max result count (display_limit option).
	 * @param string $sort Sort order in dt_asc or dt_desc.
	 * @return DomainRankHistoryResult[]
	 */
	public function domainRankHistory($params, $maxlength = '', $sort = 'dt_asc') {
		if (!is_array($params)) {
			$params = array(
				'domain' => $params,
				'display_limit' => $maxlength,
				'display_sort' => $sort
			);
		}		
		
		return $this->dispatch('domain_rank_history', DomainRankHistoryResultFactory::instance(), $params, null, $maxlength);
	}	
	
	/**
	 * Get current API units for this current account.
	 * If there is an error to get data, null is returned.
	 * @return int|NULL
	 */
	public function units() {
		$response = $this->http(self::$domain.'/users/countapiunits.html?key='.$this->key);
		$content = $response['response'];
		
		if (is_numeric($content)) {
			return $content;
		}
		
		return null;
	}
	
	/**
	 * Dispatch the request.
	 * @param string Request type.
	 * @param ResultFactory $factory Factory to make result instance.
	 * @param string|array $params Domain or multiple params.
	 * @param string $date Date to filter results, format: YYYYMM15 (display_date option).
	 * @param int $maxlength Max result count (display_limit option).
	 * @return DomainRanksResult[]
	 */
	protected function dispatch($type, ResultFactory $factory, $params, $date = '', $maxlength = '') {
		if (!is_array($params)) {
			$params = array(
				'domain' => $params,
				'display_limit' => $maxlength,
				'display_date' => $date
			);
		}
	
		return $this->request($type, $params, $factory);
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
	 * Send a http request to $url.
	 * @param string $url 
	 * @return array Array with response and http_code fields.
	 */
	protected function http($url) {
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		if (isset($_SERVER['SERVER_ADDR'])){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Real-IP', $_SERVER['SERVER_ADDR']));
		}
	
		$answer	= curl_exec($ch);
	
		$hreturn = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errcode = curl_errno($ch);
		
		return array('response' => $answer, 'http_code' => $hreturn, 'error' => $errcode);
	}	
	
	/**
	 * Send a request to Semrush API with this params.
	 * @param string $type Request type.
	 * @param array $params
	 * @return multitype:SemrushResult |boolean
	 */
	protected function request($type, $params, ResultFactory $factory) {
		$url = self::$endpoint .'/?'. $this->build($type, $params);
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		
		if (isset($_SERVER['SERVER_ADDR'])){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Real-IP', $_SERVER['SERVER_ADDR']));
		}
		
		$answer	= curl_exec($ch);
		
		$hreturn = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errcode = curl_errno($ch);
		
		if ($this->debug) {
			var_dump($url);
		}
		
		/**
		 * Success
		 */
		if ($hreturn == 200) {
			return $this->parse($answer, $params, $factory);
		}
		
		throw new SemrushException($hreturn, $answer, $url);

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