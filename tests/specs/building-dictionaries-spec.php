<?php

use Haijin\Object_Builder\Object_Builder;

$spec->describe( "When building dictionaries", function() {

    $this->it( "builds an empty object", function() {

        $object = Object_Builder::build_object( function($obj) {
            $obj->target = [];
        });

        $this->expect( $object ) ->to() ->equal( [] );

    });

    $this->it( "adds attributes to built object", function() {

        $object = Object_Builder::build_object( function($obj) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
        ]);

    });

    $this->it( "builds nested objects", function() {

        $object = Object_Builder::build_object( function($obj) {
            $obj->target = [];

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $this->build( function($obj) {
                $obj->target = [];

                $obj->street = "Evergreen 742";
            });
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds objects from a source object", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = Object_Builder::build_object( function($obj) use($source) {
            $obj->target = [];

            $obj->name = $source[ 0 ];
            $obj->last_name = $source[ 1 ];

            $address = $source[ 2 ];
            $obj->address = $this->build( function($obj) use($address) {
                $obj->target = [];

                $obj->street = $address[ 0 ] . " " . $address[ 1 ];
            });
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