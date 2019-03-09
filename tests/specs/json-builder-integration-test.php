<?php

namespace Json_Builder_Integration_Spec;

$spec->describe( "When building arrays", function() {

    $this->it( "Integration", function() {

        $action = new Action();

        $json = $action->get_json();

        $this->expect( $json ) ->to() ->be() ->exactly_like([
            "response" => [
                "api_version" => "1.0.0",
                "success" => true,
                "data" => [
                    "user" => [
                        "name" => "Lisa",
                        "last_name" => "Simpson",
                        "addresses" => [
                            [
                                "street" => 'Evergreen',
                                "number" => function($value) {
                                    $this->expect( $value ) ->to() ->be( "===" ) ->than( 742 );
                                }
                            ]
                        ]
                    ]
                ]
            ]
        ]);

    });

});

class Address
{
    public function get_street()
    {
        return "Evergreen";
    }

    public function get_number()
    {
        return "742";
    }
}

class User
{
    public function get_name()
    {
        return "Lisa";
    }

    public function get_last_name()
    {
        return "Simpson";
    }

    public function get_addresses()
    {
        return [ new Address() ];
    }
}

use Haijin\Object_Builder\Json_Builder;

class Action
{
    public function get_json()
    {
        $user = new User();

        return $this->build_json_from( $user );
    }

    protected function build_json_from($user)
    {
        $json_builder = new Json_Builder();

        return $json_builder->build( $user, function($json, $user) {

            $json->response = $this->success_response_to_json( $json, $user );

        });
    }

    protected function success_response_to_json($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->api_version = "1.0.0";

            $json->success = true;

            $json->data = [
                "user" => $this->user_to_json( $json, $user )
            ];
        }) ;
    }

    protected function user_to_json($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->name = $user->get_name();

            $json->last_name = $user->get_last_name();

            $json->addresses = array_map(
                function($each_address) use($json) {
                    return $this->address_to_json( $json, $each_address );
                },
                $user->get_addresses()
            );
        });
    }

    protected function address_to_json($json, $address)
    {
        return $json->build( $address, function($json, $address) {

            $json->street = $json->to_string( $address->get_street() );

            $json->number = $json->to_int( $address->get_number() );
        });
    }
}