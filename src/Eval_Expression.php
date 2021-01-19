<?php

namespace Easy_Plugins;

use Exception;
use ReflectionException;
use ReflectionFunction;

/**
 * Class Eval_Expression
 *
 * @noinspection PhpUnused
 * @package Easy_Plugins
 */
class Eval_Expression {

	/**
	 * Set to true to turn off warnings when evaluating expressions.
	 *
	 * @var bool
	 */
	public $suppress_errors = true;

	/**
	 * If the last evaluation failed, contains a string describing the error.
	 * Useful when suppress_errors is `true`.
	 *
	 * @var null
	 */
	public $last_error = null;

	/**
	 * Variables and Constants
	 *
	 * @var float[]
	 */
	public $v = array(
		'e'  => 2.71,
		'pi' => 3.14,
	);

	/**
	 * User-defined functions.
	 *
	 * @var array
	 */
	public $f = array();

	/**
	 * Constants.
	 *
	 * @var string[]
	 */
	public $vb = array(
		'e',
		'pi',
	);

	/**
	 * Builtin functions.
	 *
	 * @var string[]
	 */
	public $fb = array(
		'sin',
		'sinh',
		'arcsin',
		'asin',
		'arcsinh',
		'asinh',
		'cos',
		'cosh',
		'arccos',
		'acos',
		'arccosh',
		'acosh',
		'tan',
		'tanh',
		'arctan',
		'atan',
		'arctanh',
		'atanh',
		'sqrt',
		'abs',
		'ln',
		'log',
	);

	/**
	 * Function defined outside of Expression as closures.
	 *
	 * @var array
	 */
	var $functions = array();

	/**
	 * Eval_Expression constructor.
	 */
	public function __construct() {

		// Make the variables a little more accurate.
		$this->v['pi'] = pi();
		$this->v['e']  = exp( 1 );

		// Create logical functions by defining as if user-defined.
		$this->evaluate( 'if(x,y,z) = (x*y)+((1-x)*z)' );
		$this->evaluate( 'and(x,y) = x&&y' );
		$this->evaluate( 'or(x,y) = x||y' );
		$this->evaluate( 'not(x) = 1!x' );
	}

	/**
	 * A synonym for @see Eval_Expression::evaluate().
	 *
	 * @param $expr
	 *
	 * @noinspection PhpUnused
	 *
	 * @throws Exception
	 * @return bool|mixed|null
	 */
	public function e( $expr ) {

		return $this->evaluate( $expr );
	}

	/**
	 * Evaluates the expression and returns the result.
	 * If an error occurs, prints a warning and returns false.
	 * If $expr is a function assignment, returns true on success.
	 *
	 * @param $expr
	 *
	 * @throws ReflectionException
	 * @return bool|mixed|null
	 */
	public function evaluate( $expr ) {

		$this->last_error = null;

		$expr = trim( $expr );

		// Strip semicolons at the end.
		if ( substr( $expr, - 1, 1 ) == ';' ) {

			$expr = substr( $expr, 0, strlen( $expr ) - 1 );
		}

		// is it a variable assignment?
		if ( preg_match( '/^\s*([a-z]\w*)\s*=(?!~|=)\s*(.+)$/', $expr, $matches ) ) {

			if ( in_array( $matches[1], $this->vb ) ) { // make sure we're not assigning to a constant
				return $this->trigger( "Cannot assign to constant '{$matches[1]}'." );
			}

			$tmp                    = $this->pfx( $this->nfx( $matches[2] ) );
			$this->v[ $matches[1] ] = $tmp; // if so, stick it in the variable array

			return $this->v[ $matches[1] ]; // and return the resulting value

			// is it a function assignment?
		} elseif ( preg_match( '/^\s*([a-z]\w*)\s*\((?:\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*)?\)\s*=(?!~|=)\s*(.+)$/', $expr, $matches ) ) {

			// get the function name
			$fnn = $matches[1];

			// make sure it isn't built in
			if ( in_array( $matches[1], $this->fb ) ) {
				return $this->trigger( "Cannot redefine built-in function '{$matches[1]}()'." );
			}

			if ( $matches[2] != "" ) {
				$args = explode( ",", preg_replace( "/\s+/", "", $matches[2] ) ); // get the arguments
			} else {
				$args = array();
			}

			// see if it can be converted to postfix
			if ( ( $stack = $this->nfx( $matches[3] ) ) === false ) {
				return false;
			}

			// freeze the state of the non-argument variables
			for ( $i = 0; $i < count( $stack ); $i ++ ) {

				$token = $stack[ $i ];

				if ( preg_match( '/^[a-z]\w*$/', $token ) and ! in_array( $token, $args ) ) {

					if ( array_key_exists( $token, $this->v ) ) {

						$stack[ $i ] = $this->v[ $token ];

					} else {

						return $this->trigger( "Undefined variable '{$token}' in function definition." );
					}
				}
			}

			$this->f[ $fnn ] = array( 'args' => $args, 'func' => $stack );

			return true;

		} else {

			// straight up evaluation, woo
			return $this->pfx( $this->nfx( $expr ) );
		}
	}

	/**
	 * Returns an associative array of all user-defined variables and values.
	 *
	 * @noinspection PhpUnused
	 *
	 * @return float[]
	 */
	public function vars() {

		$output = $this->v;
		unset( $output['pi'] );
		unset( $output['e'] );

		return $output;
	}

	/**
	 * Returns an array of all user-defined functions.
	 *
	 * @noinspection PhpUnused
	 *
	 * @return array
	 */
	public function funcs() {

		$output = array();

		foreach ( $this->f as $fnn => $dat ) {

			$output[] = $fnn . '(' . implode( ',', $dat['args'] ) . ')';
		}

		return $output;
	}

	/**
	 * Convert infix to postfix notation.
	 *
	 * @param $expr
	 *
	 * @throws ReflectionException
	 * @return array|false
	 */
	protected function nfx($expr) {

		$index  = 0;
		$stack  = new Eval_Expression_Stack;
		$output = array(); // postfix form of expression, to be passed to pfx()
		$expr   = trim( $expr );

		$ops   = array('+', '-', '*', '/', '^', '_', '%', '>', '<', '>=', '<=', '==', '!=', '=~', '&&', '||', '!');
		$ops_r = array('+'=>0,'-'=>0,'*'=>0,'/'=>0,'%'=>0,'^'=>1,'>'=>0, '<'=>0,'>='=>0,'<='=>0,'=='=>0,'!='=>0,'=~'=>0, '&&'=>0,'||'=>0,'!'=>0); // right-associative operator?
		$ops_p = array('+'=>3,'-'=>3,'*'=>4,'/'=>4,'_'=>4,'%'=>4,'^'=>5,'>'=>2,'<'=>2, '>='=>2,'<='=>2,'=='=>2,'!='=>2,'=~'=>2,'&&'=>1,'||'=>1,'!'=>5); // operator precedence

		$expecting_op = false; // we use this in syntax-checking the expression
		// and determining when a - is a negation

		/* we allow all characters because of strings
		if (preg_match("%[^\w\s+*^\/()\.,-<>=&~|!\"\\\\/]%", $expr, $matches)) { // make sure the characters are all good
			return $this->trigger("illegal character '{$matches[0]}'");
		}
		*/

		$first_argument = false;
		//$i = 0;
		$matcher = false;

		while(1) { // 1 Infinite Loop ;)
			$op = substr(substr($expr, $index), 0, 2); // get the first two characters at the current index
			if (preg_match("/^[+\-*\/^_\"<>=%(){\[!~,](?!=|~)/", $op) || preg_match("/\w/", $op)) {
				// fix $op if it should have one character
				$op = substr($expr, $index, 1);
			}
			$single_str  = '(?<!\\\\)"(?:(?:(?<!\\\\)(?:\\\\{2})*\\\\)"|[^"])*(?<![^\\\\]\\\\)"';
			$double_str  = "(?<!\\\\)'(?:(?:(?<!\\\\)(?:\\\\{2})*\\\\)'|[^'])*(?<![^\\\\]\\\\)'";
			$regex       = "(?<!\\\\)\/(?:[^\/]|\\\\\/)+\/[imsxUXJ]*";
			$json        = '[\[{](?>"(?:[^"]|\\\\")*"|[^[{\]}]|(?1))*[\]}]';
			$number      = '[\d.]+e\d+|\d+(?:\.\d*)?|\.\d+';
			$name        = '[a-z]\w*\(?|\\$\w+';
			$parenthesis = '\\(';

			// find out if we're currently at the beginning of a number/string/object/array/variable/function/parenthesis/operand
			$ex = preg_match("%^($single_str|$double_str|$json|$name|$regex|$number|$parenthesis)%", substr($expr, $index), $match);
			/*
			if ($i++ > 1000) {
				break;
			}
			if ($ex) {
				print_r($match);
			} else {
				echo json_encode($op) . "\n";
			}
			echo $index . "\n";
			*/
			//===============
			if ($op == '[' && $expecting_op && $ex) {
				if (!preg_match("/^\[(.*)\]$/", $match[1], $matches)) {
					return $this->trigger( 'Invalid array access.');
				}
				$stack->push('[');
				$stack->push($matches[1]);
				$index += strlen($match[1]);
				//} elseif ($op == '!' && !$expecting_op) {
				//    $stack->push('!'); // put a negation on the stack
				//    $index++;
			} elseif ($op == '-' and !$expecting_op) { // is it a negation instead of a minus?
				$stack->push('_'); // put a negation on the stack
				$index++;
			} elseif ($op == '_') { // we have to explicitly deny this, because it's legal on the stack
				return $this->trigger("Illegal character '_'."); // but not in the input expression
			} elseif ($ex && $matcher && preg_match("%^" . $regex . "$%", $match[1])) {
				$stack->push('"' . $match[1] . '"');
				$index += strlen($match[1]);
				$op = null;
				$expecting_op = false;
				$matcher = false;
				break;
				//===============
			} elseif (((in_array($op, $ops) or $ex) and $expecting_op) or in_array($op, $ops) and !$expecting_op or
			          (!$matcher && $ex && preg_match("%^" . $regex . "$%", $match[1]))) {
				// heart of the algorithm:
				while($stack->count > 0 and ($o2 = $stack->last()) and in_array($o2, $ops) and ($ops_r[$op] ? $ops_p[$op] < $ops_p[$o2] : $ops_p[$op] <= $ops_p[$o2])) {
					$output[] = $stack->pop(); // pop stuff off the stack into the output
				}
				// many thanks: http://en.wikipedia.org/wiki/Reverse_Polish_notation#The_algorithm_in_detail
				$stack->push($op); // finally put OUR operator onto the stack
				$index += strlen($op);
				$expecting_op = false;
				$matcher = $op == '=~';
				//===============
			} elseif ($op == ')' and $expecting_op || !$ex) { // ready to close a parenthesis?
				$arg_count = 0;
				while (($o2 = $stack->pop()) != '(') { // pop off the stack back to the last (
					if (is_null($o2)) {
						return $this->trigger("Unexpected ')'.");
					} else {
						$arg_count++;
						$output[] = $o2;
					}
				}
				if (preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches)) { // did we just close a function?
					$fnn = $matches[1]; // get the function name
					$arg_count += $stack->pop(); // see how many arguments there were (cleverly stored on the stack, thank you)
					$output[] = $stack->pop(); // pop the function and push onto the output
					if (in_array($fnn, $this->fb)) { // check the argument count
						if($arg_count > 1)
							return $this->trigger("Too many arguments ({$arg_count} given, 1 expected).");
					} elseif (array_key_exists($fnn, $this->f)) {
						if ($arg_count != count($this->f[$fnn]['args']))
							return $this->trigger("Wrong number of arguments ({$arg_count} given, " . count($this->f[$fnn]['args']) . " expected) " . json_encode($this->f[$fnn]['args']));
					} elseif (array_key_exists($fnn, $this->functions)) {
						$func_reflection = new ReflectionFunction($this->functions[$fnn]);
						$count = $func_reflection->getNumberOfParameters();
						if ($arg_count != $count)
							return $this->trigger("Wrong number of arguments ({$arg_count} given, {$count} expected).");
					} else { // did we somehow push a non-function on the stack? this should never happen
						return $this->trigger( 'Internal error.');
					}
				}
				$index++;
				//===============
			} elseif ($op == ',' and $expecting_op) { // did we just finish a function argument?
				while (($o2 = $stack->pop()) != '(') {
					if (is_null($o2)) return $this->trigger("Unexpected ','"); // oops, never had a (
					else $output[] = $o2; // pop the argument expression stuff and push onto the output
				}
				// make sure there was a function
				if (!preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches))
					return $this->trigger("Unexpected ','");
				if ($first_argument) {
					$first_argument = false;
				} else {
					$stack->push($stack->pop()+1); // increment the argument count
				}
				$stack->push('('); // put the ( back on, we'll need to pop back to it again
				$index++;
				$expecting_op = false;
				//===============
			} elseif ($op == '(' and !$expecting_op) {
				$stack->push('('); // that was easy
				$index++;
				//$allow_neg = true;
				//===============
			} elseif ($ex and !$expecting_op) { // do we now have a function/variable/number?
				$expecting_op = true;
				$val = $match[1];
				if ($op == '[' || $op == "{" || preg_match("/null|true|false/", $match[1])) {
					$output[] = $val;
				} elseif (preg_match("/^([a-z]\w*)\($/", $val, $matches)) { // may be func, or variable w/ implicit multiplication against parentheses...
					if (in_array($matches[1], $this->fb) or
					    array_key_exists($matches[1], $this->f) or
					    array_key_exists($matches[1], $this->functions)) { // it's a func
						$stack->push($val);
						$stack->push(0);
						$stack->push('(');
						$expecting_op = false;
					} else { // it's a var w/ implicit multiplication
						$val = $matches[1];
						$output[] = $val;
					}
				} else { // it's a plain old var or num
					$output[] = $val;
					if (preg_match("/^([a-z]\w*)\($/", $stack->last(3))) {
						$first_argument = true;
						while (($o2 = $stack->pop()) != '(') {
							if (is_null($o2)) return $this->trigger( 'Unexpected error.'); // oops, never had a (
							else $output[] = $o2; // pop the argument expression stuff and push onto the output
						}
						// make sure there was a function
						if (!preg_match("/^([a-z]\w*)\($/", $stack->last(2), $matches))
							return $this->trigger( 'Unexpected error.');

						$stack->push($stack->pop()+1); // increment the argument count
						$stack->push('('); // put the ( back on, we'll need to pop back to it again
					}
				}
				$index += strlen($val);
				//===============
			} elseif ($op == ')') { // miscellaneous error checking
				return $this->trigger("Unexpected ')'.");
			} elseif (in_array($op, $ops) and !$expecting_op) {
				return $this->trigger("Unexpected operator '{$op}'.");
			} else { // I don't even want to know what you did to get here
				return $this->trigger("An unexpected error occured " . json_encode($op) . " " . json_encode($match) . " ". ($ex?'true':'false') . " " . $expr);
			}
			if ($index == strlen($expr)) {
				if (in_array($op, $ops)) { // did we end with an operator? bad.
					return $this->trigger("Operator '{$op}' lacks operand.");
				} else {
					break;
				}
			}
			while (substr($expr, $index, 1) == ' ') { // step the index past whitespace (pretty much turns whitespace
				$index++;                             // into implicit multiplication if no operator is there)
			}

		}
		while (!is_null($op = $stack->pop())) { // pop everything off the stack and push onto output
			if ($op == '(') return $this->trigger("Expecting ')'"); // if there are (s on the stack, ()s were unbalanced
			$output[] = $op;
		}
		return $output;
	}

	/**
	 * Evaluate postfix notation.
	 *
	 * @param       $tokens
	 * @param array $vars
	 *
	 * @throws ReflectionException
	 * @return false|mixed|null
	 */
	protected function pfx($tokens, $vars = array()) {

		if ( $tokens == false ) {
			return false;
		}

		$stack = new Eval_Expression_Stack();

		foreach ( $tokens as $token ) {

			// if the token is a binary operator, pop two values off the stack, do the operation, and push the result back on
			if (in_array($token, array('+', '-', '*', '/', '^', '<', '>', '<=', '>=', '==', '&&', '||', '!=', '=~', '%'))) {

				$op2 = $stack->pop();
				$op1 = $stack->pop();

				switch ( $token ) {

					case '+':
						if ( is_string( $op1 ) || is_string( $op2 ) ) {
							$stack->push( (string) $op1 . (string) $op2 );
						} else {
							$stack->push( $op1 + $op2 );
						}
						break;
					case '-':
						$stack->push( $op1 - $op2 );
						break;
					case '*':
						$stack->push( $op1 * $op2 );
						break;
					case '/':
						if ( $op2 == 0 ) {
							return $this->trigger( 'Division by zero.' );
						}
						$stack->push( $op1 / $op2 );
						break;
					case '%':
						$stack->push( $op1 % $op2 );
						break;
					case '^':
						$stack->push( pow( $op1, $op2 ) );
						break;
					case '>':
						$stack->push( $op1 > $op2 );
						break;
					case '<':
						$stack->push( $op1 < $op2 );
						break;
					case '>=':
						$stack->push( $op1 >= $op2 );
						break;
					case '<=':
						$stack->push( $op1 <= $op2 );
						break;
					case '==':
						if ( is_array( $op1 ) && is_array( $op2 ) ) {
							$stack->push( json_encode( $op1 ) == json_encode( $op2 ) );
						} else {
							$stack->push( $op1 == $op2 );
						}
						break;
					case '!=':
						if ( is_array( $op1 ) && is_array( $op2 ) ) {
							$stack->push( json_encode( $op1 ) != json_encode( $op2 ) );
						} else {
							$stack->push( $op1 != $op2 );
						}
						break;
					case '=~':
						$value = @preg_match( $op2, $op1, $match );

						if ( ! is_int( $value ) ) {
							return $this->trigger( 'Invalid regex ' . json_encode( $op2 ) );
						}
						$stack->push( $value );
						for ( $i = 0; $i < count( $match ); $i ++ ) {
							$this->v[ '$' . $i ] = $match[ $i ];
						}
						break;
					case '&&':
						$stack->push( $op1 ? $op2 : $op1 );
						break;
					case '||':
						$stack->push( $op1 ? $op1 : $op2 );
						break;
				}

				// if the token is a unary operator, pop one value off the stack, do the operation, and push it back on
			} elseif ($token == '!') {

				$stack->push(!$stack->pop());

			} elseif ($token == '[') {

				$selector = $stack->pop();
				$object   = $stack->pop();

				if (is_object($object)) {

					$stack->push($object->$selector);

				} elseif (is_array($object)) {

					$stack->push($object[$selector]);

				} else {

					return $this->trigger( 'Invalid object for selector.' );
				}
			} elseif ($token == "_") {

				$stack->push(-1*$stack->pop());

				// if the token is a function, pop arguments off the stack, hand them to the function, and push the result back on
			} elseif (preg_match("/^([a-z]\w*)\($/", $token, $matches)) { // it's a function!
				$fnn = $matches[1];
				if (in_array($fnn, $this->fb)) { // built-in function:
					if (is_null($op1 = $stack->pop())) return $this->trigger( 'internal error.');
					$fnn = preg_replace("/^arc/", "a", $fnn); // for the 'arc' trig synonyms
					if ($fnn == 'ln') $fnn = 'log';
					$stack->push($fnn($op1)); // perfectly safe variable function call
				} elseif (array_key_exists($fnn, $this->f)) { // user function
					// get args
					$args = array();
					for ($i = count($this->f[$fnn]['args'])-1; $i >= 0; $i--) {
						if ($stack->empty()) {
							return $this->trigger("internal error " . $fnn . " " . json_encode($this->f[$fnn]['args']));
						}
						$args[$this->f[$fnn]['args'][$i]] = $stack->pop();
					}
					$stack->push($this->pfx($this->f[$fnn]['func'], $args)); // yay... recursion!!!!
				} else if (array_key_exists($fnn, $this->functions)) {
					$reflection = new ReflectionFunction($this->functions[$fnn]);
					$count = $reflection->getNumberOfParameters();
					for ($i = $count-1; $i >= 0; $i--) {
						if ($stack->empty()) {
							return $this->trigger( 'Internal error.');
						}
						$args[] = $stack->pop();
					}
					$stack->push($reflection->invokeArgs($args));
				}
				// if the token is a number or variable, push it on the stack
			} else {
				if (preg_match('/^([\[{](?>"(?:[^"]|\\")*"|[^[{\]}]|(?1))*[\]}])$/', $token) ||
				    preg_match("/^(null|true|false)$/", $token)) { // json
					//return $this->trigger("invalid json " . $token);
					if ($token == 'null') {
						$value = null;
					} elseif ($token == 'true') {
						$value = true;
					} elseif ($token == 'false') {
						$value = false;
					} else {
						$value = json_decode($token);
						if ($value == null) {
							return $this->trigger("Invalid JSON " . $token);
						}
					}
					$stack->push($value);
				} elseif (is_numeric($token)) {
					$stack->push(0+$token);
				} else if (preg_match("/^['\\\"](.*)['\\\"]$/", $token)) {
					$stack->push(json_decode(preg_replace_callback("/^['\\\"](.*)['\\\"]$/", function($matches) {
						$m = array("/\\\\'/", '/(?<!\\\\)"/');
						$r = array("'", '\\"');
						return '"' . preg_replace($m, $r, $matches[1]) . '"';
					}, $token)));
				} elseif (array_key_exists($token, $this->v)) {
					$stack->push($this->v[$token]);
				} elseif (array_key_exists($token, $vars)) {
					$stack->push($vars[$token]);
				} else {
					return $this->trigger("Undefined variable '{$token}'.");
				}
			}
		}

		// when we're out of tokens, the stack should have a single element, the final result
		if ( $stack->count != 1 ) {
			return $this->trigger( 'Internal error.' );
		}

		return $stack->pop();
	}

	/**
	 * Throw an exception on error.
	 *
	 * @param $msg
	 *
	 * @throws Exception
	 * @return false
	 */
	protected function trigger( $msg ) {

		$this->last_error = $msg;

		if ( ! $this->suppress_errors ) {

			throw new Exception( $msg );
		}

		return false;
	}

}

/**
 * Internal use only.
 *
 * Class Eval_Expression_Stack
 *
 * @package Easy_Plugins
 */
final class Eval_Expression_Stack {

	/**
	 * @var array
	 */
	private $stack = array();

	/**
	 * @var int
	 */
	public $count = 0;

	public function push( $val ) {

		$this->stack[ $this->count ] = $val;
		$this->count++;
	}

	public function pop() {

		if ( $this->count > 0 ) {
			$this->count --;

			return $this->stack[ $this->count ];
		}

		return null;
	}

	public function empty() {

		return empty( $this->stack );
	}

	public function last( $n = 1 ) {

		if ( isset( $this->stack[ $this->count - $n ] ) ) {
			return $this->stack[ $this->count - $n ];
		}

		return;
	}

}
