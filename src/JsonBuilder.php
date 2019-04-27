<?php

namespace Haijin\ObjectBuilder;

/**
 * An ObjectBuilder subclass to build JSON objects.
 */
class JsonBuilder extends ObjectBuilder
{
    public function eval(...$params)
    {
        $this->setTarget(new \stdclass());

        return parent::eval(...$params);
    }

    /// Converters

    /**
     * Converts a value to an integer.
     *
     * Example:
     *
     *      $json = JsonBuilder::->buildJson( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->toInt( "123" );
     *      });
     *
     * @param object $value The value to convert to an integer.
     *
     * @return int The value converted to an integer.
     */
    public function toInt($value)
    {
        return (int)$value;
    }

    /**
     * Converts a value to a string.
     *
     * Example:
     *
     *      $json = JsonBuilder::->buildJson( function($json) {
     *          $json->target = [];
     *
     *          $json->number = $json->toString( 123 );
     *      });
     *
     * @param object $value The value to convert to a string.
     *
     * @return string The value converted to a string.
     */
    public function toString($value)
    {
        return (string)$value;
    }
}