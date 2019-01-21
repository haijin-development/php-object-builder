<?php

namespace Haijin\Object_Builder;

use Haijin\Instantiator\Create;

class Object_Builder implements \ArrayAccess
{
    //// Class methods

    /**
     * Builds and returns a built object.
     *
     * Example:
     *
     *      $object = Object_Builder::build_object( function($obj) {
     *          $obj->target = [];
     *
     *          $obj->name = "Lisa";
     *          $obj->last_name = "Simpson";
     *          $obj->address = $this->build( function($obj) {
     *              $obj->target = [];
     *
     *              $obj->street_name = "Evergreen";
     *              $obj->street_number = "742";
     *          });
     *      });
     * 
     * @param object $value An optional value to be the source of the target being built.
     * @param callable $closure The building closure.
     * @param object $binding Optional - an binding object for the closure evaluations.
     *
     * @return object The built object.
     */
    static public function build_object(...$params)
    {
        $builder = Create::a( self::class )->with();

        return $builder->build( ...$params );
    }

    //// Instance methods

    /**
     * The binding to evaluate the closures. Defaults to this instance
     */
    protected $binding;
    /**
     * The object being built.
     */
    public $target;
    /**
     * An optional source to build the target.
     */
    public $value;

    /// Initializing

    /**
     * Initializes this Object_Builder.
     */
    public function __construct()
    {
        $this->binding = $this;
        $this->target = null;
        $this->value = null;
    }

    /// Accessors

    /**
     * Sets the default binding for the closure evaluations.
     *
     * @param object $binding An object to be the default binding for closure evaluations.
     *
     * @return Object_Builder Returns $this object.
     */
    public function set_binding($binding)
    {
        $this->binding = $binding;

        return $this;
    }

    /**
     * Gets the object being built.
     *
     * @return object The object being built.
     */
    public function get_target()
    {
        return $this->target;
    }

    /**
     * Gets the optional source value for building the target.
     *
     * @return object Returns the optional building source value.
     */
    public function get_value()
    {
        return $this->value;
    }

    /**
     * Delegates the accessing of attributes and properties to the target object being built.
     *
     * @param string $name The name of the target attribute or public property.
     * @param object $value The value to set to the target attribute attribute or public property.
     */
    public function __set($name, $value)
    {
        $this->target[ $name ] = $value;
    }

    /**
     * Delegates the call of a method to the target object being built.
     *
     * @param string $method_name The name of the method.
     * @param array $params The parameters of the method call.
     *
     * @return object Returns the result of calling the method to the target object.
     */
    public function __call($method_name, $params)
    {
        return $this->target->$method_name( ... $params );
    }

    /// Converting DSL

    /**
     * Evaluates a closure with the object building DSL.
     *
     * Method signature
     *
     *      public function build($optional_value, $closure, $optional_binding);
     *
     * @param object $value Optional - A value to be set as the current value.
     * @param callable $closure The closure with the building definition.
     * @param object $binding Optional - An optional binding for the closure evaluation.
     *
     * @return object Returns the building target.
     */
    public function build( ... $params )
    {
        $params = \Haijin\Ordered_Collection::with_all( $params );
        $value = null;
        $closure = null;
        $binding = null;

        if( ! ( $params[0] instanceof \Closure ) ) {
            $value = $params[0];
            $closure = $params[1];
        } else {
            $value = $this->value;
            $closure = $params[0];
        }

        if( ! ( $params[-1] instanceof \Closure ) && $params[-1] !== null ) {
            $binding = $params[-1];
        } else {
            $binding = $this->binding;
        }

        $builder = Create::a( get_class( $this ) )->with();

        return $builder->_build(null, $value, $closure, $binding);
    }

    /**
     * Builds and returns an object using a custom Object_Builder class or instance.
     *
     * @param string|Object_Builder $object_builder The class or instance of the Object_Builder.
     * @param ... $params The list of parameters to pass along to the method call( ...$params ) of the Object_Builder.
     *
     * @return object The build object.
     */
    public function build_with($object_builder, ... $params)
    {
        if( is_string( $object_builder ) ) {
            $object_builder = Create::a( $object_builder )->with();
        }

        $object_builder->evaluate( ... $params );

        return $object_builder->target;
    }

    public function _build($target, $value, $closure, $binding)
    {
        $this->target = $target === null ? [] : $target;
        $this->value = $value;
        $this->binding = $binding;

        $closure->call( $this->binding, $this );

        return $this->target;
    }

    /**
     * Creates and returns a Value_Holder wrapper on a value to later call a conversion method.
     *
     * Example:
     *
     *      $object = (new AppObjectBuilder() )->build( function($obj) use($user) {
     *          $obj->target = [];
     *
     *          $obj->address = $this->convert( $user->get_address() ) ->to_address();
     *      });
     *
     * @param object $value The value to be convereted.
     *
     * @return Value_Holder A wrapper that accepts conversion methods. See the example.
     */
    public function convert( $value )
    {
        return Create::a( Value_Holder::class )->with( $value, $this );
    }

    /// ArrayAccess implementation
    
    public function offsetExists($offset)
    {
        throw Create::an( \Exception::class )->with( "Unsupported operation" );
    }

    public function offsetGet($offset)
    {
        return $this->target[ $offset ];
    }

    public function offsetSet($offset, $value)
    {
        if( $offset === null ) {
            $offset = count( $this->target );
        }

        $this->target[ $offset ] = $value;
    }

    public function offsetUnset($offset)
    {
        unset( $this->target[ $offset ] );
    }
}