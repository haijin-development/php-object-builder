<?php

namespace CustomObjectBuildersTest;

use Haijin\ObjectBuilder\ObjectBuilder;

class CustomObjectBuildersTest extends \PHPUnit\Framework\TestCase
{
    use \Haijin\Testing\AllExpectationsTrait;

    public function testBuildWithObjectBuilderInstance()
    {
        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( new AddressBuilder(), $source[2] );
        });

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);
    }

    public function testBuildWithObjectBuilderClass()
    {
        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( "CustomObjectBuildersTest\AddressBuilder", $source[2] );
        });

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);
    }

    public function testBuildWithObjectBuilderConverterMethod()
    {
        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = (new AppObjectBuilder() )->build( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->convert( $source[2] ) ->to_address();
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

class AddressBuilder extends ObjectBuilder
{
    public function evaluate($source)
    {
        $this->target = [];

        $this->street = $source[ 0 ] . " " . $source[ 1 ];
    }
}

class AppObjectBuilder extends ObjectBuilder
{
    public function to_address( $source )
    {
        return $this->build( function($obj) use($source) {
            $obj->target = [];

            $obj->street = $source[ 0 ] . " " . $source[ 1 ];
        });
    }
}