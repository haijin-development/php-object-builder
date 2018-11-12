<?php

namespace BuildingObjectsTest;

use Haijin\ObjectBuilder\ObjectBuilder;

class BuildingObjectsTest extends \PHPUnit\Framework\TestCase
{
    use \Haijin\Testing\AllExpectationsTrait;

    public function testBuildAnEmptyObject()
    {
        $user = ObjectBuilder::build_object( function($obj) {
            $obj->target = new User();
        });

        $this->assertEquals( true, is_a( $user, 'BuildingObjectsTest\User' ) );
    }

    public function testAddAttributesToBuiltObject()
    {
        $user = ObjectBuilder::build_object( function($obj) {
            $obj->target = new User();

            $obj->set_name( "Lisa" );
            $obj->set_last_name( "Simpson" );
        });

        $this->expectObjectToBeLike( $user, [
            "get_name()" => "Lisa",
            "get_last_name()" => "Simpson",
        ]);
    }

    public function testAddNestedAttributesToBuiltObject()
    {
        $user = ObjectBuilder::build_object( function($obj) {
            $obj->target = new User();

            $obj->set_name( "Lisa" );
            $obj->set_last_name( "Simpson" );

            $obj->set_address(
                $this->build( function($obj) {
                    $obj->target = new Address();
                    
                    $obj->set_street( "Evergreen" );
                    $obj->set_number( 742 );
                }) 
            );
        });

        $this->expectObjectToBeLike( $user, [
            "get_name()" => "Lisa",
            "get_last_name()" => "Simpson",
            "get_address()" => [
                "get_street()" => "Evergreen",
                "get_number()" => 742
            ]
        ]);
    }
}

class Address
{
    protected $street;
    protected $number;

    public function set_street($street)
    {
        $this->street = $street;
    }

    public function get_street()
    {
        return $this->street;
    }

    public function set_number($number)
    {
        $this->number = $number;
    }

    public function get_number()
    {
        return $this->number;
    }
}

class User
{
    protected $name;
    protected $last_name;
    protected $address;

    public function set_name($name)
    {
        $this->name = $name;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function set_last_name($last_name)
    {
        $this->last_name = $last_name;
    }

    public function get_last_name()
    {
        return $this->last_name;
    }

    public function get_address()
    {
        return $this->address;
    }

    public function set_address($address)
    {
        $this->address = $address;
    }
}
