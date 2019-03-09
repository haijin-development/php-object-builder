<?php

use Haijin\Debugger;

\Haijin\Specs\Specs_Runner::configure( function($specs) {

});

function inspect($object)
{
    return Debugger::inspect( $object );
}