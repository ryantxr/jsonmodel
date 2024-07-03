# Ryantxr\JsonModel\Model

## Overview

The Model class provides a JSON wrapper for parsing, manipulating, and exporting JSON data using dot notation and array syntax. It supports nested properties and arrays, with validation for key access to ensure only numeric indices are allowed within square brackets.
Installation

To use this class, ensure you have PHP 7.4 or any version of PHP 8. You can include this class in your project via Composer.
Composer Configuration

## Install via Composer:

    composer require ryantxr/jsonmodel

## Usage
Class Instantiation

You can instantiate the Model class with an optional JSON string:

```php

use Ryantxr\JsonModel\Model;

$jsonInput = '
{
    "foo" : {
        "bar" : {
            "baz": [
                {"a": 100},
                {"b": 200},
                {"c": 300}
            ],
            "fiz" : {
                "x": "xyzzy", 
                "y": "yyzzy",
                "z": "zyzzy"
            },
            "buzz": [
                91, 92, 93, 94, 95
            ]
        }
    }
}';

$model = new Model($jsonInput);

$model = new \Ryantxr\Json\Model($jsonInput);
```

## Methods

### get(string $var): mixed

Retrieves the value of a property using dot notation and array syntax.

#### Example:

    $value = $model->get('foo.bar.baz[0].a'); // Returns 100

Throws:

    InvalidArgumentException if the key format is invalid.

### set(string $var, $value): void

Sets the value of a property using dot notation and array syntax.

#### Example:

    $model->set('foo.bar.buzz[1]', '1001');

Throws:

    InvalidArgumentException if the key format is invalid.

### isset(string $var): bool

Checks if a variable exists using dot notation and array syntax.

#### Example:

    $exists = $model->isset('foo.bar.baz'); // Returns true

### export(): string

Exports the current data as a JSON string.

#### Example:

    $jsonOutput = $model->export();

### parse(string $json): void

Parses and stores the JSON object.

#### Example:

    $model->parse($jsonInput);

### parseKeys(string $keyString): array

Parses the keys and determines their types. Validates keys to ensure only numeric indices are allowed within square brackets.

Throws:

    InvalidArgumentException if a key format is invalid.

Example Usage

    use Ryantxr\JsonModel\Model;

    $jsonInput = '
    {
        "foo" : {
            "bar" : {
                "baz": [
                    {"a": 100},
                    {"b": 200},
                    {"c": 300}
                ],
                "fiz" : {
                    "x": "xyzzy", 
                    "y": "yyzzy",
                    "z": "zyzzy"
                },
                "buzz": [
                    91, 92, 93, 94, 95
                ]
            }
        }
    }';

    $model = new Model($jsonInput);

    echo $model->get('foo.bar.baz[0].a'); // Output: 100
    $model->set('foo.bar.buzz[1]', '1001');
    echo $model->get('foo.bar.buzz[1]'); // Output: 1001

    if ($model->isset('foo.bar')) {
        echo 'Property exists';
    }

    echo $model->export(); // Output: JSON string

## Unit Testing

The Model class can be tested using PHPUnit. Below is an example test class.

The Model class can be tested using PHPUnit. Below is an example test class.
Example Test Class

    <?php

    use PHPUnit\Framework\TestCase;
    use Ryantxr\JsonModel\Model;
    use InvalidArgumentException;

    class ModelTest extends TestCase
    {
        private $jsonInput;

        protected function setUp(): void
        {
            $this->jsonInput = '
            {
                "foo" : {
                    "bar" : {
                        "baz": [
                            {"a": 100},
                            {"b": 200},
                            {"c": 300}
                        ],
                        "fiz" : {
                            "x": "xyzzy", 
                            "y": "yyzzy",
                            "z": "zyzzy"
                        },
                        "buzz": [
                            91, 92, 93, 94, 95
                        ]
                    }
                }
            }';
        }

        public function testGetTopLevelObject()
        {
            $model = new Model($this->jsonInput);
            $this->assertIsObject($model->get('foo'));
        }

        public function testGetNestedObject()
        {
            $model = new Model($this->jsonInput);
            $this->assertIsObject($model->get('foo.bar'));
        }

        public function testGetDeepNestedObject()
        {
            $model = new Model($this->jsonInput);
            $this->assertIsArray($model->get('foo.bar.baz'));
        }

        public function testGetArrayElement()
        {
            $model = new Model($this->jsonInput);
            $this->assertEquals(100, $model->get('foo.bar.baz[0].a'));
        }

        public function testGetNestedValue()
        {
            $model = new Model($this->jsonInput);
            $this->assertEquals('zyzzy', $model->get('foo.bar.fiz.z'));
        }

        public function testSetAndGet()
        {
            $model = new Model();
            $model->set('foo.bar.baz', 'testValue');
            $this->assertEquals('testValue', $model->get('foo.bar.baz'));
        }

        public function testSetAndGetArr()
        {
            $model = new Model();
            $model->set('foo.bar.buzz[1]', '1001');
            $this->assertEquals('1001', $model->get('foo.bar.buzz[1]'));
        }

        public function testIsset()
        {
            $model = new Model($this->jsonInput);
            $this->assertTrue($model->isset('foo.bar'));
            $this->assertFalse($model->isset('foo.bar.nonexistent'));
        }

        public function testExport()
        {
            $model = new Model($this->jsonInput);
            $this->assertJson($model->export());
        }

        public function testInvalidKeyGet()
        {
            $this->expectException(InvalidArgumentException::class);
            $model = new Model($this->jsonInput);
            $model->get('foo.bar.baz[abc]');
        }

        public function testInvalidKeySet()
        {
            $this->expectException(InvalidArgumentException::class);
            $model = new Model($this->jsonInput);
            $model->set('foo.bar.baz[abc]', 999);
        }

        public function testInvalidKeyWithSpecialCharsGet()
        {
            $this->expectException(InvalidArgumentException::class);
            $model = new Model($this->jsonInput);
            $model->get('foo.bar.baz[@#$]');
        }

        public function testInvalidKeyWithSpecialCharsSet()
        {
            $this->expectException(InvalidArgumentException::class);
            $model = new Model($this->jsonInput);
            $model->set('foo.bar.baz[@#$]', 999);
        }

        public function testNonExistentArrayIndexGet()
        {
            $model = new Model($this->jsonInput);
            $this->assertNull($model->get('foo.bar.baz[10]'));
        }

        public function testNonExistentPropertyGet()
        {
            $model = new Model($this->jsonInput);
            $this->assertNull($model->get('foo.bar.nonexistent'));
        }
    }

### Run the tests using PHPUnit:

    vendor/bin/phpunit --bootstrap vendor/autoload.php tests/ModelTest.php

## License

This project is licensed under the MIT License.
