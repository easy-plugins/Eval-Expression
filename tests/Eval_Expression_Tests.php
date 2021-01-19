<?php

use Easy_Plugins\Evaluate\Expression;
use PHPUnit\Framework\TestCase;

/**
 * Testing eval math
 */
class Eval_Expression_Tests extends TestCase {

	public function testReadmeExamples() {

		$data = [
			'-8*(5/2)^2*(1-sqrt(4))-8'       => 42,
			'3*f(2,b)'                       => 6,
			'3*f(42,a)'                      => 4532.92746449864,
			'10 < 20 || 20 > 30 && 10 == 10' => true,
			'2 + 2 == 4'                     => true,
			'2 + 2 < 4'                      => false,
			'2 + 2 >= 4'                     => true,
		];

		$math = new Expression();

		$math->evaluate( 'a = e^(ln(pi))' );
		$math->evaluate( 'b = 1' );
		$math->evaluate( 'f(x,y) = x^2 + y^2 - 2*x*y + 1' );

		foreach ( $data as $formula => $result ) {

			$this->assertEquals( $math->evaluate( $formula ), $result );
		}
	}

	public function testAddition() {

		$math = new Expression();
		$this->assertSame( 4, $math->evaluate( '2 + 2' ) );
	}

	public function testSubtraction() {

		$math = new Expression();
		$this->assertSame( 2, $math->evaluate( '4 - 2' ) );
	}

	public function testMultiplication() {

		$math = new Expression();
		$this->assertSame( 4, $math->evaluate( '2 * 2' ) );
	}

	public function testDivision() {

		$math = new Expression();
		$this->assertSame( 2, $math->evaluate( '4 / 2' ) );
	}

	public function testAdditionFP() {

		$math = new Expression();
		$this->assertSame( 4.1, $math->evaluate( '2 + 2.1' ) );
	}

	public function testSubtractionFP() {

		$math = new Expression();
		$this->assertSame( 2.5, $math->evaluate( '4.5 - 2' ) );
	}

	public function testMultiplicationFP() {

		$math = new Expression();
		$this->assertSame( 4.5, $math->evaluate( '2.25 * 2' ) );
	}

	public function testDivisionFP() {

		$math = new Expression();
		$this->assertSame( 4.5, $math->evaluate( '9 / 2' ) );
	}

	public function testLogicEqualsTrue() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 == 1' ) );
	}

	public function testLogicEqualsFalse() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 == 2' ) );
	}

	public function testLogicNotEqualsTrue() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 != 1' ) );
	}

	public function testLogicNotEqualsFalse() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 != 0' ) );
		$this->assertSame( true, $math->evaluate( '1 != 2' ) );
	}

	public function testLogicGTTrue() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 > 0' ) );
	}

	public function testLogicGTFalse() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 > 2' ) );
	}

	public function testLogicGTETrue() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 >= 0' ) );
		$this->assertSame( true, $math->evaluate( '1 >= 1' ) );
	}

	public function testLogicGTEFalse() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 >= 2' ) );
	}

	public function testLogicLTTrue() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 < 5' ) );
	}

	public function testLogicLTFalse() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 < 1' ) );
	}

	public function testLogicLTETrue() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 <= 2' ) );
		$this->assertSame( true, $math->evaluate( '1 <= 1' ) );
	}

	public function testLogicLTEFalse() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 <= 0' ) );
	}

	public function testLogicEquationEquals() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 + 1 == 2' ) );
		$this->assertSame( false, $math->evaluate( '1 + 1 == 1' ) );
	}

	public function testLogicEquationNotEquals() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 + 1 != 2' ) );
		$this->assertSame( true, $math->evaluate( '1 + 1 != 1' ) );
	}

	public function testLogicEquationGT() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 + 1 > 2' ) );
		$this->assertSame( true, $math->evaluate( '1 + 1 > 1' ) );
	}

	public function testLogicEquationGTE() {

		$math = new Expression();
		$this->assertSame( false, $math->evaluate( '1 + 1 >= 3' ) );
		$this->assertSame( true, $math->evaluate( '1 + 1 >= 2' ) );
		$this->assertSame( true, $math->evaluate( '1 + 1 >= 1' ) );
	}

	public function testLogicEquationLT() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 + 1 < 3' ) );
		$this->assertSame( false, $math->evaluate( '1 + 1 < 2' ) );
	}

	public function testLogicEquationLTE() {

		$math = new Expression();
		$this->assertSame( true, $math->evaluate( '1 + 1 <= 3' ) );
		$this->assertSame( true, $math->evaluate( '1 + 1 <= 2' ) );
		$this->assertSame( false, $math->evaluate( '1 + 1 <= 1' ) );
	}

	/**
	 * @test
	 * @dataProvider moduloOperatorData
	 */
	public function shouldSupportModuloOperator( $formula, $values, $expectedResult ) {

		$math = new Expression();

		foreach ( $values as $k => $v ) {
			$math->v[ $k ] = $v;
		}

		$this->assertEquals( $expectedResult, $math->evaluate( $formula ) );
	}

	public function moduloOperatorData() {

		return array(
			array(
				'a%b', // 9%3 => 0
				array( 'a' => 9, 'b' => 3 ),
				0,
			),
			array(
				'a%b', // 10%3 => 1
				array( 'a' => 10, 'b' => 3 ),
				1,
			),
			array(
				'10-a%(b+c*d)', // 10-10%(7-2*2) => 9
				array( 'a' => '10', 'b' => 7, 'c' => - 2, 'd' => 2 ),
				9,
			),
		);
	}

	/**
	 * @test
	 * @dataProvider doubleMinusData
	 */
	public function shouldConsiderDoubleMinusAsPlus( $formula, $values, $expectedResult ) {

		$math = new Expression();

		foreach ( $values as $k => $v ) {
			$math->v[ $k ] = $v;
		}

		$this->assertEquals(
			$expectedResult,
			$math->evaluate( $formula )
		);
	}

	public function doubleMinusData() {

		return array(
			array(
				'a+b*c--d', // 1+2*3--4 => 1+6+4 => 11
				array(
					'a' => 1,
					'b' => 2,
					'c' => 3,
					'd' => 4,
				),
				11,
			),
			array(
				'a+b*c--d', // 1+2*3---4 => 1+6-4 => 3
				array(
					'a' => 1,
					'b' => 2,
					'c' => 3,
					'd' => - 4,
				),
				3,
			),
		);
	}

	public function testIntegers() {

		$ints = array( "100", "3124123", (string) PHP_INT_MAX, "-1000" );
		$math = new Expression();

		for ( $i = 0; $i < count( $ints ); $i ++ ) {
			$result = $math->evaluate( $ints[ $i ] );
			$this->assertEquals( $result, intval( $ints[ $i ] ) );
		}

	}

	public function testFloats() {

		$ints = array( "10.10", "0.01", ".1", "1.", "-100.100", "1.10e2", "-0.10e10" );
		$math = new Expression();

		for ( $i = 0; $i < count( $ints ); $i ++ ) {
			$result = $math->evaluate( $ints[ $i ] );
			$this->assertEquals( $result, floatval( $ints[ $i ] ) );
		}
	}

	public function arrayTest( $array ) {

		$math = new Expression();

		for ( $i = 0; $i < count( $array ); $i ++ ) {
			$result = $math->evaluate( $array[ $i ] );
			$this->assertEquals( $result, eval( "return " . $array[ $i ] . ";" ) );
		}
	}

	public function testAritmeticOperators() {

		$expressions = array(
			"-10",
			"20+20",
			"-20+20",
			"-0.1+0.1",
			".1+.1",
			"1.+1.",
			"0.1+(-0.1)",
			"20*20",
			"-20*20",
			"20*(-20)",
			"1.*1.",
			".1*.1",
			"20-20",
			"-20-20",
			"20/20",
			"-20/20",
			"10%20",
			"10%9",
			"20%9",
		);

		$this->arrayTest( $expressions );

		try {
			$math = new Expression();
			$math->evaluate( '10/0' );
			$this->assertTrue( false ); // will fail if evaluate don't throw exception
		}
		catch ( Exception $e ) {
			$this->assertTrue( true );
		}
	}

	public function testSemicolon() {

		$math = new Expression();
		$result = $math->evaluate( "10+10;" );
		$this->assertEquals( $result, "20" );
	}

	public function testBooleanComparators() {

		$expressions = array(
			"10 == 10",
			"10 == 20",
			"0.1 == 0.1",
			"0.1 == 0.2",
			"10 != 10",
			"20 != 10",
			"0.1 != 0.1",
			"0.1 != 0.2",
			"10 < 10",
			"20 < 10",
			"10 < 20",
			"0.1 < 0.2",
			"0.2 < 0.1",
			"0.1 < 0.1",
			"10 > 10",
			"20 > 10",
			"10 > 20",
			"0.1 > 0.2",
			"0.2 > 0.1",
			"0.1 > 0.1",
			"10 <= 10",
			"20 <= 10",
			"10 <= 20",
			"0.1 <= 0.2",
			"0.2 <= 0.1",
			"0.1 <= 0.1",
			"10 >= 10",
			"20 >= 10",
			"10 >= 20",
			"0.1 >= 0.2",
			"0.2 >= 0.1",
			"0.1 >= 0.1",
		);

		$this->arrayTest( $expressions );
	}

	public function testBooleanOperators() {

		$expressions = array(
			"10 == 10 && 10 == 10",
			"10 != 10 && 10 != 10",
			"10 == 20 && 10 == 10",
			"10 == 10 && 10 == 20",
			"0.1 == 0.1 && 0.1 == 0.1",
			"0.1 == 0.2 && 0.1 == 0.1",
			"0.1 == 0.1 && 0.1 == 0.2",
			"10 == 10 || 10 == 10",
			"10 == 20 || 10 == 10",
			"10 == 10 || 10 == 20",
			"0.1 == 0.1 || 0.1 == 0.1",
			"0.1 == 0.2 || 0.1 == 0.1",
			"0.1 == 0.1 || 0.1 == 0.2",
		);

		$this->arrayTest( $expressions );

		$expressions = array(
			'("foo" == "foo") && "a" || "b"' => "a",
			'("foo" == "bar") && "a" || "b"' => "b",
		);

		$math = new Expression();

		foreach ( $expressions as $expression => $value ) {

			$result = $math->evaluate( $expression );
			$this->assertEquals( $result, $value );
		}
	}

	public function testPriorityOperands() {

		$data = [
			'2+2*2'                   => 6,
			'2-2+2*2+2/2*-1+2'        => 5,
			'2+1 > 2+2'               => false,
			'2+1 < 2+2'               => true,
			'2+2*2-2/2 >= 2*2+-2/2*2' => true,
		];

		$math = new Expression();

		foreach ( $data as $formula => $result ) {

			$this->assertEquals( $math->evaluate( $formula ), $result );
		}
	}

	public function testKeywords() {

		$expressions = array(
			"1 == true",
			"true == true",
			"false == false",
			"false != true",
			"null == null",
			"null != true",
		);

		$math = new Expression();

		foreach ( $expressions as $expression ) {

			$result = $math->evaluate( $expression );
			$this->assertTrue( (bool) $result );
		}

		$expressions = array( "foo = true" => true, "foo = false" => false, "foo = null" => null );

		foreach ( $expressions as $expression => $value ) {

			$math = new Expression();
			$result = $math->evaluate( $expression );
			$result = $math->evaluate( "foo" );
			$this->assertEquals( $result, $value );
		}

	}

	public function testNegation() {

		$expressions = array( "!(10 == 10)", "!1", "!0" );
		$this->arrayTest( $expressions );
	}

	public function testStrings() {

		$expressions = array(
			'"foo" == "foo"',
			'"foo\\"bar" == "foo\\"bar"',
			'"f\\"oo" != "f\\"oo"',
			'"foo\\"" != "foo\\"bar"',
			"'foo\"bar' == 'foo\"bar'",
			"'foo' == 'foo'",
			"'foo\\'foo' != 'foo'",
			'"foo\\\\" == "foo\\\\"',
			"'foo\\\\' == 'foo\\\\'",
		);

		$this->arrayTest( $expressions );

		$expressions = array(
			'"foo" + "bar"'       => 'foobar',
			"'foo' + 'bar'"       => 'foobar',
			'"foo\\"bar" + "baz"' => "foo\"barbaz",
		);

		$math = new Expression();

		foreach ( $expressions as $expression => $value ) {

			$result = $math->evaluate( $expression );
			$this->assertEquals( $result, $value );
		}

		$result = $math->evaluate( '"foo\"ba\\\\\\"r" =~ /foo/' );
		$this->assertEquals( (boolean) $result, true );
	}

	public function testMatchers() {

		$expressions = array(
			'"Foobar" =~ /([fo]+)/i' => 'Foo',
			'"foobar" =~ /([0-9]+)/' => null,
			'"1020" =~ /([0-9]+)/'   => '1020',
			'"1020" =~ /([a-z]+)/'   => null,
		);

		foreach ( $expressions as $expression => $group ) {

			$math = new Expression();

			$result = $math->evaluate( $expression );

			if ( $group == null ) {

				$this->assertEquals( (boolean) $result, false );
			}

			if ( $group != null ) {
				$this->assertEquals( $math->evaluate( '$1' ), $group );
			}
		}
	}

	public function testVariableAssignment() {

		$expressions = array(
			'foo = "bar"'              => array( 'var' => 'foo', 'value' => 'bar' ),
			'foo = 10'                 => array( 'var' => 'foo', 'value' => 10 ),
			'foo = 0.1'                => array( 'var' => 'foo', 'value' => 0.1 ),
			'foo = 10 == 10'           => array( 'var' => 'foo', 'value' => 1 ),
			'foo = 10 != 10'           => array( 'var' => 'foo', 'value' => 0 ),
			'foo = "foo" =~ "/[fo]+/"' => array( 'var' => 'foo', 'value' => 1 ),
			'foo = 10 + 10'            => array( 'var' => 'foo', 'value' => 20 ),
		);

		foreach ( $expressions as $expression => $object ) {

			$math = new Expression();

			$math->evaluate( $expression );
			$this->assertEquals( $math->evaluate( $object['var'] ), $object['value'] );
		}
	}

	public function testVariables() {

		$math = new Expression();

		$math->v += [
			'f_price'                 => 500,
			'f_width'                 => 500,
			'f_turndown_0_2_f_count'  => 2,
			'f_length_metal_f_length' => 1400,
		];

		$formula = 'f_price*(f_width+f_turndown_0_2_f_count*10)*f_length_metal_f_length/1000000';
		$this->assertEquals( $math->evaluate( $formula ), 364 );
	}

	public function testJSON() {

		$expressions = array(
			array( "foo" => "bar" ),
			array( 'foo\\"bar' => "baz" ),
			array( "foo}" => "bar" ),
			array( 10, 20, 30, 40 ),
			array( 10, "]", 30 ),
			array( 10, array( "foo" => "bar" ), 30 ),
		);

		$math = new Expression();

		foreach ( $expressions as $expression ) {

			$json   = json_encode( $expression );
			$result = $math->evaluate( $json );
			$this->assertEquals( json_encode( $result ), $json );
		}

		$expressions = array(
			'{"foo":"bar"} == {"foo":"bar"}'     => true,
			'{"foo2":"bar2"} == {"foo": "bar"}'  => false,
			'{"f}o2":"ba{r2"} != {"foo": "bar"}' => true,
			'[10,20] != [20,30]'                 => true,
		);

		foreach ( $expressions as $expression => $value ) {
			$result = $math->evaluate( $expression );
			$this->assertEquals( (bool) $result, $value );
		}

		$expressions = array(
			'{"foo":"bar"}["foo"]' => "bar",
			'[10,20][0]'           => 10,
		);

		foreach ( $expressions as $expression => $value ) {

			$result = $math->evaluate( $expression );
			$this->assertEquals( $result, $value );
		}

		$math->evaluate( 'foo = {"foo": "bar"}' );
		$result = $math->evaluate( 'foo["foo"]' );
		$this->assertEquals( $result, 'bar' );
	}

	public function testCustomFunctions() {

		$functions = array(
			'square(x) = x*x'                        => array(
				'square(10)'        => 100,
				'square(-10)'       => 100,
				'square(10) == 100' => 1,
			),
			'plus(x,y) = x+y'                        => array(
				'plus(-1, -1)'   => - 2,
				'plus(10, 10)'   => 20,
				'plus(-10, -10)' => - 20,
			),
			'string() = "foo"'                       => array(
				'string() =~ "/[fo]+/"' => 1,
				'string() == "foo"'     => 1,
				'string() != "bar"'     => 1,
			),
			'number(x) = x =~ "/^[0-9]+$/"'          => array(
				'number("10")'    => 1,
				'number("10foo")' => 0,
			),
			'logic(x, y) = x == "foo" || x == "bar"' => array(
				'logic( "foo", 1 )' => 1,
				'logic("bar", 1)'   => 1,
				'logic("lorem", 1)' => 0,
			),
		);

		foreach ( $functions as $function => $object ) {

			$math = new Expression();

			$math->evaluate( $function );

			foreach ( $object as $fn => $value ) {

				$this->assertEquals( $math->evaluate( $fn ), $value );
			}
		}
	}

	public function testCustomClosures() {

		$math = new Expression();

		$math->functions['even'] = function( $a ) {

			return $a % 2 == 0;
		};

		$values = array( 10 => true, 20 => true, 1 => false, 3 => false, 4 => true );

		foreach ( $values as $number => $value ) {

			$this->assertEquals( (bool) $math->evaluate( "even($number)" ), $value );
		}
	}

	public function testCustomClosureDate() {

		$math = new Expression();

		$math->functions['date'] = function( $a ) {

			return strtotime( $a );
		};

		$firstDayThisMonth = strtotime( 'first day of this month 00:00:00 UTC' );

		$values = array(
			968544000          => '10 September 2000 00:00:00 UTC',
			$firstDayThisMonth => 'first day of this month 00:00:00 UTC',
		);

		foreach ( $values as $timestamp => $string ) {

			$this->assertEquals( $timestamp, $math->evaluate( "date('{$string}')" ) );
		}
	}
}
