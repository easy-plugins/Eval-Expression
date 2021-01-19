<?php

namespace Easy_Plugins\Evaluate;

/**
 * Class Stack
 *
 * @package Easy_Plugins\Eval_Expression
 */
final class Stack {

	/**
	 * @var array
	 */
	private $stack = array();

	/**
	 * @var int
	 */
	public $count = 0;

	/**
	 * @param mixed $val
	 */
	public function push( $val ) {

		$this->stack[ $this->count ] = $val;
		$this->count++;
	}

	/**
	 * @return mixed|null
	 */
	public function pop() {

		if ( $this->count > 0 ) {
			$this->count --;

			return $this->stack[ $this->count ];
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function empty() {

		return empty( $this->stack );
	}

	/**
	 * @param int $n
	 *
	 * @return mixed|null
	 */
	public function last( $n = 1 ) {

		$key = $this->count - $n;

		return array_key_exists( $key, $this->stack ) ? $this->stack[ $key ] : null;
	}

}
