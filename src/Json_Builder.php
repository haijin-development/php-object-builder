<?php

namespace Haijin\Object_Builder;

/**
 * An Object_Builder subclass to build JSON objects.
 */
class Json_Builder extends Object_Builder
{
    public function eval(...$params)
    {
        $this->set_target( new \stdclass() );

        return parent::eval( ...$params );
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