<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Traits;

use Illuminate\Support\Collection;

trait AjaxDataAccess
{
    public function getAjaxData(): Collection
    {
        $repeaterData = $this->query->get('_repeater_data');

        return collect($repeaterData ?? []);
    }
}
