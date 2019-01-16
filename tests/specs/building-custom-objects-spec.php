<?php

namespace Custom_Object_Builders_Spec;

use Haijin\Object_Builder\Object_Builder;

$spec->describe( "When building custom objects", function() {

    $this->it( "builds the object with an Object_Builder instance", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = Object_Builder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( new Address_Builder(), $source[2] );
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an Object_Builder class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = Object_Builder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build_with( "Custom_Object_Builders_Spec\Address_Builder", $source[2] );
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an Object_Builder converter method class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = (new App_Object_Builder() )->build( function($obj) use($source) {
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

class Address_Builder extends Object_Builder
{
    public function evaluate($source)
    {
        $this->target = [];

        $this->street = $source[ 0 ] . " " . $source[ 1 ];
    }
}

class App_Object_Builder extends Object_Builder
{
    public function to_address( $source )
    {
        return $this->build( function($obj) use($source) {
            $obj->target = [];

            $obj->street = $source[ 0 ] . " " . $source[ 1 ];
        });
    }
}