<?php

use MBIct\Factory\Factory;

require_once 'models.php';

class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function can_load_factories_from_directrory()
    {
        $factory = new Factory;

        $factory->loadFactories(__DIR__ . "/_factories");

        $this->assertTrue($factory->has(TestModelA::class), 'Expected class TestModelA to be registered');
        $this->assertTrue($factory->has(TestModelB::class), 'Expected class TestModelB to be registered');
    }

    /**
     * @test
     * @expectedException  MBIct\Factory\Exceptions\DirectoryNotFoundException
     */
    public function throws_exception_when_loading_from_non_existing_directory()
    {
        $factory = new Factory;

        $factory->loadFactories(__DIR__ . "/invalid_directroy");
    }

    /**
     * @test
     */
    public function has_will_return_true_on_registered_classes()
    {
        $factory = new Factory;

        $factory->define(TestModelA::class, function () {
        });

        $this->assertTrue($factory->has(TestModelA::class));
    }

    /**
     * @test
     */
    public function has_will_return_false_on_not_registered_classes()
    {
        $factory = new Factory;

        $factory->define(TestModelA::class, function () {
        });

        $this->assertFalse($factory->has(TestModelB::class));
    }

    /**
     * @test
     * @expectedException  MBIct\Factory\Exceptions\DefinitionAlreadyDefinedException
     */
    public function throws_exception_when_defining_a_already_existing_definition()
    {
        $factory = new Factory;

        $factory->define(TestModelA::class, function () {
        });
        $factory->define(TestModelA::class, function () {
        });
    }

    /**
     * @test
     * @expectedException  MBIct\Factory\Exceptions\DefinitionNotCallableException
     */
    public function throws_exception_when_defining_a_non_callable_generator()
    {
        $factory = new Factory;

        $factory->define(TestModelA::class, null);
    }

    /**
     * @test
     * @expectedException  MBIct\Factory\Exceptions\ModelNotFoundException
     */
    public function throws_exception_when_creating_a_non_existing_class()
    {
        $factory = new Factory;
        $factory->define("nonExistingModelclass", function () {
        });

        $factory->create("nonExistingModelclass");
    }

    /**
     * @test
     * @expectedException MBIct\Factory\Exceptions\DefinitionNotFoundException
     */
    public function throws_exception_when_creating_a_non_registered_class()
    {
        $factory = new Factory;

        $factory->create(TestModelA::class);
    }

    /**
     * @test
     */
    public function create_object_with_default_generator()
    {
        $factory = new Factory;
        $factory->define(TestModelA::class, function () {
            return [
                'id'   => 123,
                'name' => 'test',
            ];
        });

        $object = $factory->create(TestModelA::class);

        $this->assertInstanceOf(TestModelA::class, $object);
        $this->assertEquals($object->getId(), 123);
        $this->assertEquals($object->getName(), 'test');
    }

    /**
     * @test
     */
    public function create_object_with_overridden_data_attributes()
    {
        $factory = new Factory;
        $factory->define(TestModelA::class, function () {
            return [
                'id'   => 123,
                'name' => 'test',
            ];
        });

        $object = $factory->create(TestModelA::class, [
            'id'   => 999,
            'name' => 'override',
        ]);

        $this->assertInstanceOf(TestModelA::class, $object);
        $this->assertEquals($object->getId(), 999);
        $this->assertEquals($object->getName(), 'override');
    }

    /**
     * @test
     * @expectedException  MBIct\Factory\Exceptions\SetterNotCallableException
     * @expectedExceptionMessage not_existing_key
     */
    public function generates_error_when_setting_invalid_properties_from_generator()
    {
        $factory = new Factory;
        $factory->define(TestModelA::class, function () {
            return [
                'not_existing_key' => 'test',
            ];
        });

        $object = $factory->create(TestModelA::class, [
            'id'   => 999,
            'name' => 'override',
        ]);

        $this->assertInstanceOf(TestModelA::class, $object);
        $this->assertEquals($object->getId(), 999);
        $this->assertEquals($object->getName(), 'override');
    }

    /**
     * @test
     */
    public function generator_gets_a_instance_of_faker_and_the_provided_data()
    {
        $faker = $this->getMock(\Faker\Generator::class);
        $data = ['id' => '123'];
        $factory = new Factory;
        $factory->setFaker($faker);
        $observer = $this->getMockBuilder('test')
            ->setMethods(['__invoke'])
            ->getMock();
        $observer->expects($this->once())
            ->method('__invoke')
            ->with($this->equalTo($faker), $this->equalTo($data))
            ->will($this->returnValue([]));

        $factory->define(TestModelA::class, $observer);

        $factory->create(TestModelA::class, $data);
    }

    /**
     * @test
     */
    public function faker_getter_and_setter()
    {
        $faker = $this->getMock(\Faker\Generator::class);
        $factory = new Factory;

        $factory->setFaker($faker);

        $this->assertSame($faker, $factory->getFaker());
    }
}
