<?php

namespace Haijin\Object_Builder;

use Haijin\Instantiator\Create;

/**
 * An Object_Builder subclass to build JSON objects.
 */
class Json_Builder extends Object_Builder
{
    //// Class methods

    /**
     * Builds and returns a JSON object.
     *
     * Example:
     *
     *      $json = Json_Builder::build_json( function($json) {
     *          $json->target = [];
     *
     *          $json->name = "Lisa";
     *          $json->last_name = "Simpson";
     *          $json->address = $this->build( function($json) {
     *              $json->target = [];
     *
     *              $json->street_name = "Evergreen";
     *              $json->street_number = "742";
     *          });
     *      });
     * 
     * @param callable $closure The building closure
     * @param object $binding Optional - an binding object for the closure evaluations
     *
     * @return array An associative or indexed array with the JSON built object.
     */
    static public function build_json($closure, $binding = null)
    {
        $builder = Create::a( self::class )->with();

        return $builder->build( $closure, $binding );
    }

    //// Instance methods

    public function _build($target, $value, $closure, $binding)
    {
        if( $target === null ) {
            $target = [];
        }

        return parent::_build( $target, $value, $closure, $binding );
    }

    /// Converters

    /**
     * Converts a value to an integer.
     *
     * Example:
     *
     *      $json = Json_Builder::->build_json( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->convert( "123" ) ->to_int();
     *      });
     *
     *      // or
     *
     *      $json = Json_Builder::->build_json( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->to_int( "123" );
     *      });
     *
     * @param object $value The value to convert to an integer.
     *
     * @return int The value converted to an integer.
     */
    public function to_int( $value )
    {
        return (int) $value;
    }

    /**
     * Converts a value to a string.
     *
     * Example:
     *
     *      $json = Json_Builder::->build_json( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->convert( 123 ) ->to_string();
     *      });
     *
     *      // or
     *
     *      $json = Json_Builder::->build_json( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->to_string( 123 );
     *      });
     *
     * @param object $value The value to convert to a string.
     *
     * @return string The value converted to a string.
     */
    public function to_string( $value )
    {
        return (string) $value;
    }
}