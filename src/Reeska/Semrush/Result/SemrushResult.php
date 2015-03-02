<?php
namespace Reeska\Semrush\Result;

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