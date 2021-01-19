# DESCRIPTION

Use the Eval_Expression class when you want to evaluate mathematical expressions from untrusted sources. You can define your own variables and functions, which are stored in the object. Try it, it's fun!

Based on the work by:
- Miles Kaufmann <http://www.twmagic.com/>
- Lee Eden <https://www.phpclasses.org/discuss/package/2695/thread/4/>
- Jakub Jankiewicz <https://github.com/jcubic/expression.php>
- Josh Marshall <https://github.com/joshbmarshall/evalmathlogic>

Includes units test by:
- Jakub Jankiewicz <https://github.com/jcubic/expression.php>
- Josh Marshall <https://github.com/joshbmarshall/evalmathlogic>
- Daniel Bojdo <https://github.com/dbojdo/eval-math>

This library can be installed as a WordPress plugin. It adds a shortcode that can be used to evaluate expressions.

Example: `[eval]2+3*4[/eval]`

The shortcode accepts the following parameter:
- `precision` :: The result will be rounded to the supplied integer. The default is `2`.

# EXAMPLES

```php
include 'Expression.php';
include 'Stack.php';

$math = new Easy_Plugins\Evaluate\Expression;

// Basic evaluation:
$result = $math->evaluate('2+2'); // 4

// Supports: order of operation; parentheses; negation; built-in functions:
$result = $math->evaluate('-8*(5/2)^2*(1-sqrt(4))-8'); // 42

// Support of booleans.
$result = $math->evaluate('10 < 20 || 20 > 30 && 10 == 10'); // true

// Supports logic.
$result = $math->evaluate('2 + 2 == 4'); // true
$result = $math->evaluate('2 + 2 < 4'); // false
$result = $math->evaluate('2 + 2 >= 4'); // true

// Support for strings and match (regexes can be like in PHP or like in JavaScript).
$result = $math->evaluate('"Foo,Bar" =~ /^([fo]+),(bar)$/i');

// Previous call will create $0 for whole match match and $1,$2 for groups.
$result = $math->evaluate('$2'); // 'Bar'

// Create your own variables:
$math->evaluate('a = e^(ln(pi))');

// Create your own functions:
$math->evaluate('f(x,y) = x^2 + y^2 - 2x*y + 1');

// Use user-defined variables and functions.
$result = $math->evaluate('3*f(42,a)'); // 4532.92746449864

// Use custom closures.
$math->functions['date'] = function( $a ) {

    return strtotime( $a );
};

$result = $math->evaluate("date('first day of this month 00:00:00 UTC')"); // 968544000
```

# METHODS

```$math->evalute($expr)```

Evaluates the expression and returns the result.
If an error occurs, prints a warning and returns false.
If $expr is a function assignment, returns true on success.

```$math->e($expr)```

A synonym for $m->evaluate().

```$math->vars()```

Returns an associative array of all user-defined variables and values.

```$math->funcs()```

Returns an array of all user-defined functions.

# PARAMETERS

```$math->suppress_errors```

Set to `false` to turn on warnings when evaluating expressions.

```$math->last_error```

If the last evaluation failed, contains a string describing the error.
(Useful when suppress_errors is on).

# BUILT-IN FUNCTIONS

The following mathematical functions can be called within the expression:

- sin(n)
- sinh(n)
- arcsin(n)
- asin(n)
- arcsinh(n)
- asinh(n)
- cos(n)
- cosh(n)
- arccos(n)
- acos(n)
- arccosh(n)
- acosh(n)
- tan(n)
- tanh(n)
- arctan(n)
- atan(n)
- arctanh(n)
- atanh(n)
- sqrt(n)
- abs(n)
- ln(n)
- log(n)

The following logical functions have also been defined:

- if(a,b,c) - a is a logical expression, b returned if true, c if false
- or(a,b)
- and(a,b)
- not(a)
