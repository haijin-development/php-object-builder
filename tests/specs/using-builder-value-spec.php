<?php

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe( "When building arrays", function() {

    $this->it( "passing values", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = ObjectBuilder::build_object( $source, function($obj) {
            $obj->target = [];

            $obj->name = $obj->value[ 0 ];
            $obj->last_name = $obj->value[ 1 ];

            $obj->address = $this->build( $obj->value[2], function($obj) {
                $obj->target = [];

                $obj->street = $obj->value[ 0 ] . " " . $obj->value[ 1 ];
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