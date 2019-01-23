<?php

namespace Nakukryskin\OrchidRepeaterField\Facades;

use Illuminate\Support\Facades\Facade;

class OrchidRepeaterField extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'repeater-field';
    }
}
