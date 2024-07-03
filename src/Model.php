<?php
namespace Ryantxr\JsonModel;

use stdClass;
use InvalidArgumentException;
class Model
{
    /** @var object */
    private $data;

    /**
     * @param string|null $json
     */
    public function __construct(?string $json=null)
    {
        if ( is_string($json) ) {
            $this->parse($json);
        } else {
            $this->data = new stdClass;
        }
    }

    /**
     * parses and stores the json object
     * @param string $json
     */
    public function parse(string $json)
    {
        $this->data = json_decode($json);
    }

    /**
     * Retrieves the value of a property using dot notation
     * @param string $var
     * @return mixed
     */
    public function get(string $var)
    {
        $keys = $this->parseKeys($var);
        $value = $this->data;

        foreach ($keys as $key) {
            if ($key->type === 'property') {
                if (is_object($value) && isset($value->{$key->name})) {
                    $value = $value->{$key->name};
                } else {
                    return null;
                }
            } elseif ($key->type === 'array') {
                if (is_array($value) && isset($value[$key->index])) {
                    $value = $value[$key->index];
                } else {
                    return null;
                }
            }
        }

        return $value;
    }

    /**
     * Magic getter
     */
    public function __get($var)
    {
        return $this->get($var);
    }

    /**
     * 
     */
    public function set($var, $value)
    {
        if (!is_object($this->data)) {
            $this->data = new stdClass;
        }
        
        $keys = $this->parseKeys($var);
        $current = &$this->data;
        
        foreach ($keys as $key) {
            if ($key->type === 'property') {
                if (!isset($current->{$key->name})) {
                    $current->{$key->name} = new stdClass;
                }
                $current = &$current->{$key->name};
            } elseif ($key->type === 'array') {
                if (!is_array($current)) {
                    $current = [];
                }
                $current = &$current[$key->index];
            }
        }
        
        $current = $value;
    }
    
    /**
     * Magic setter for setting properties using dot notation and array syntax
     */
    public function __set($var, $value)
    {
        $this->set($var, $value);
    }

    /**
     * determines if a variable exists
     * @param string $var
     * @return bool
     */
    public function isset($var): bool
    {
        $keys = preg_split('/(\.|\[|\])/', $var, -1, PREG_SPLIT_NO_EMPTY);
        $value = $this->data;

        foreach ($keys as $key) {
            if (is_object($value) && isset($value->{$key})) {
                $value = $value->{$key};
            } elseif (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return false;
            }
        }

        return true;
    }


    /**
     * Parses the keys and determines their types
     * @param string $keyString
     * @return array
     * @throws InvalidArgumentException
     */
    private function parseKeys(string $keyString): array
    {
        $keys = explode('.', $keyString);
        $parsedKeys = [];

        foreach ($keys as $key) {
            if (preg_match('/^([a-zA-Z_]\w*)$/', $key)) {
                $parsedKeys[] = (object)[
                    'type' => 'property',
                    'name' => $key
                ];
            } elseif (preg_match('/^([a-zA-Z_]\w*)\[(\d+)\]$/', $key, $matches)) {
                $parsedKeys[] = (object)[
                    'type' => 'property',
                    'name' => $matches[1]
                ];
                $parsedKeys[] = (object)[
                    'type' => 'array',
                    'index' => (int)$matches[2]
                ];
            } else {
                throw new InvalidArgumentException("Invalid key: $key");
            }
        }

        return $parsedKeys;
    }

    /**
     * Exports data back to a json string.
     */
    public function export(): string
    {
        if ( ! is_object($this->data) ) {
            return '{}';
        }
        return json_encode($this->data);
    }

    /**
     * Somewhat smart echo.
     * @param mixed $x
     */
    protected function e($x)
    {
        if ( is_string($x) || is_int($x) || is_float($x) ) {
            echo "\n" . $x . "\n";
        } else {
            echo "\n" . print_r($x, true) . "\n";
        }
    }
}
/*
$jsonInput = '
{
    "foo" : {
        "bar" : {
            "baz": {
                "a": 100,
                "b": 200,
                "c": 300
            },
            "fiz" : {
                "x": "xyzzy", 
                "y": "yyzzy",
                "z": "zyzzy"
            }
        }
    }
}
';
$p = new \Ryantxr\JsonModel\Model($jsonInput);

$p->get('foo'); // returns object
$p->get('foo.bar'); // returns object
$p->get('foo.bar.baz); // return object
$p->get('foo.bar.baz.a'); // returns 100
$p->get('foo.bar.fiz.z'); // returns 'zyzzy'


{
"foo": {
    "bar": {
        "baz" : {
            "abc": 999
        }
    }
}}


Use a structure like this:

input: 'foo.bar.bax[1]'
// After splitting with .
[
'foo',
'bar',
'bax[1]'
];
// Scan for invalid input
// throw exception if needed.
// run some code to produce this:
$keys = [
 (object)['type' => 'property', 'name' => 'foo'],
 (object)['type' => 'property', 'name' => 'bar'],
 (object)['type' => 'array', 'name' => 'baz[1]', 'aname' => 'baz', 'index' => 1],
]


*/



