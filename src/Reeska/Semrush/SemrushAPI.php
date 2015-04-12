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
	protected static $endpoint = 'http://api.semrush.com';
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
		
		return $this->dispatch('domain_rank_history', DomainRankHistoryResultFactory::instance(), $params, $date, $maxlength);
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
	 * Send a request to Semrush API with this params.
	 * @param string $type Request type.
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