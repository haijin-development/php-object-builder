<?php

use Haijin\ObjectBuilder\ObjectBuilder;

class UsingBuilderValueTestTest extends \PHPUnit\Framework\TestCase
{
    use \Haijin\Testing\AllExpectationsTrait;

    public function testPassingValues()
    {
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

        $this->expectObjectToBeLike( $object, [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);
    }
}