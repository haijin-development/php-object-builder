<?php

use Haijin\ObjectBuilder\ObjectBuilder;

class BuildingArraysTest extends \PHPUnit\Framework\TestCase
{
    use \Haijin\Testing\AllExpectationsTrait;

    public function testBuildAnEmptyArray()
    {
        $array = ObjectBuilder::build_object( function($array) {
            $array->target = [];
        });

        $this->assertEquals( [], $array );
    }

    public function testAddAttributesToBuiltObject()
    {
        $array = ObjectBuilder::build_object( function($array) {
            $array->target = [];

            $array[] = "Lisa";
            $array[] = "Simpson";
        });

        $this->assertEquals( [ "Lisa", "Simpson" ], $array );
    }

    public function testBuilNestedArrays()
    {
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

        $this->assertEquals( [ "Lisa", "Simpson", [ "Evergreen", "742" ] ], $array );
    }

    public function testBuilArraysFromSource()
    {
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

        $this->assertEquals( [ "Lisa", "Simpson", [ "Evergreen", "742" ] ], $array );
    }
}