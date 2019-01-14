<?php

namespace CustomObjectBuildersTest;

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe( "When building custom objects", function() {

    $this->it( "builds the object with an ObjectBuilder instance", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( new AddressBuilder(), $source[2] );
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an ObjectBuilder class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( "CustomObjectBuildersTest\AddressBuilder", $source[2] );
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an ObjectBuilder converter method class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = (new AppObjectBuilder() )->build( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->convert( $source[2] ) ->to_address();
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

});

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