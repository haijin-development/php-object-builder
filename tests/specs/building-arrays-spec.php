<?php

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe( "When building arrays", function() {

    $this->it( "builds an empty array", function() {

        $array = ObjectBuilder::build_object( function($array) {
            $array->target = [];
        });

        $this->expect( $array ) ->to() ->equal( [] );

    });

    $this->it( "adds items to the array", function() {

        $array = ObjectBuilder::build_object( function($array) {
            $array->target = [];

            $array[] = "Lisa";
            $array[] = "Simpson";
        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson"
        ]);

    });

    $this->it( "builds nested arrays", function() {

        $array = ObjectBuilder::build_object( function($array) {
            $array->target = [];

            $array[] = "Lisa";
            $array[] = "Simpson";
            $array[] = $this->build( function($array) {
                $array->target = [];

                $array[] = "Evergreen";
                $array[] = "742";
            });
        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson",
            [ 
                "Evergreen", "742"
            ] 
        ]);

    });

    $this->it( "builds arrays from a source object", function() {

        $user = [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ];

        $array = ObjectBuilder::build_object( function($array) use($user) {
            $array->target = [];

            $array[] = $user[ "name" ];
            $array[] = $user[ "last_name" ];

            $address = $user[ "address" ];
            $array[] = $this->build( function($array) use($address) {
                $array->target = [];

                $parts = explode(" ", $address[ "street" ]);

                $array[] = $parts[ 0 ];
                $array[] = $parts[ 1 ];
            });
        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson",
            [ 
                "Evergreen", "742"
            ] 
        ]);

    });

});