<?php

use PHPUnit\Framework\TestCase;
use Ryantxr\JsonModel\Model;

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

    public function testSetArrayElement()
    {
        $model = new Model($this->jsonInput);
        $model->set('foo.bar.buzz[2]', 101);
        $this->assertEquals(101, $model->get('foo.bar.buzz[2]'));
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
        $this->expectException(\InvalidArgumentException::class);
        $model = new Model($this->jsonInput);
        $model->get('foo.bar.baz[abc]');
    }

    public function testInvalidKeySet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $model = new Model($this->jsonInput);
        $model->set('foo.bar.baz[abc]', 999);
    }

    public function testInvalidKeyWithSpecialCharsGet()
    {
        $this->expectException(\InvalidArgumentException::class);
        $model = new Model($this->jsonInput);
        $model->get('foo.bar.baz[@#$]');
    }

    public function testInvalidKeyWithSpecialCharsSet()
    {
        $this->expectException(\InvalidArgumentException::class);
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
