<?php

use Haijin\ObjectBuilder\ObjectBuilder;

class BuildingDictionariesTest extends \PHPUnit\Framework\TestCase
{
    use \Haijin\Testing\AllExpectationsTrait;

    public function testBuildAnEmptyObject()
    {
        $object = ObjectBuilder::build_object( function($obj) {
            $obj->target = [];
        });

        $this->assertEquals( [], $object );
    }

    public function testAddAttributesToBuiltObject()
    {
        $object = ObjectBuilder::build_object( function($obj) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";
        });

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
        ]);
    }

    public function testBuilNestedObjects()
    {
        $object = ObjectBuilder::build_object( function($obj) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build( function($obj) {
                $obj->target = [];

                $obj->street = "Evergreen 742";
            });
        });

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);
    }

    public function testBuilObjectsFromSource()
    {
        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = $source[ 0 ];
            $obj->last_name = $source[ 1 ];

            $address = $source[ 2 ];
            $obj->address = $this->build( function($obj) use($address) {
                $obj->target = [];

                $obj->street = $address[ 0 ] . " " . $address[ 1 ];
            });
        });

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);
    }
}