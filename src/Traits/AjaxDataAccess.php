<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Traits;

trait AjaxDataAccess
{
    public function getAjaxData(): ?array
    {
        return $this->query->get('_repeater_data', []);
    }
}
