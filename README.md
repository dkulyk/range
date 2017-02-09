# Sequence

## Quick Example

Install the package via composer by adding this section to the composer.json file:

```JSON
"require": {
    "dkulyk/sequence": "~1.0"
},
```

This is a tiny script to get a feeling of how Sequence works.

```php
<?php
require_once __DIR__.'/vendor/autoload.php';

use DKulyk\Sequence\Sequence;

function fibonacci(&$value, $a = 0, $b = 1)
{
    $value = $a + $b;
    return function (&$v) use ( $value, $b) {
        return fibonacci($v, $b, $value);
    };
}


$i = (new Sequence('fibonacci'))
    ->limit(10);

foreach ($i as $k => $v) {
    echo $k, ' => ', $v, PHP_EOL;
}
```

and the output of this program will be:

    0 => 1
    1 => 2
    2 => 3
    3 => 5
    4 => 8
    5 => 13
    6 => 21
    7 => 34
    8 => 55
    9 => 89


This is just a tiny bit of all the things that can be accomplished with Sequence.

