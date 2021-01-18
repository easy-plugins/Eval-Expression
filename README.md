# DESCRIPTION

Use the Eval_Expression class when you want to evaluate mathematical expressions
from untrusted sources.	 You can define your own variables and functions,
which are stored in the object.	 Try it, it's fun!

# SYNOPSIS

```php
include 'Eval_Expression.php';

$math = new Easy_Plugins\Eval_Expression;

// Basic evaluation:
$result = $math->evaluate('2+2');

// Supports: order of operation; parentheses; negation; built-in functions:
$result = $math->evaluate('-8(5/2)^2*(1-sqrt(4))-8');

// Create your own variables:
$math->evaluate('a = e^(ln(pi))');

// Create your own functions:
$math->evaluate('f(x,y) = x^2 + y^2 - 2x*y + 1');

// Use user-defined variables and functions.
$result = $math->evaluate('3*f(42,a)');
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
- the date(y,m,d,h,m) returns a timestamp in unix format (seconds since 1970) by utilising PHP's `strtotime()` function on "yyyy-mm-dd hh:mm:00 UTC"
