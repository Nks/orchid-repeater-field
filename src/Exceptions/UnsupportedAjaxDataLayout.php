<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class UnsupportedAjaxDataLayout extends \Exception
{
    #[Pure]
    public function __construct(string $layout, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            "To use ajaxData your layout \"{$layout}\" should use trait \Nakukryskin\OrchidRepeaterField\Traits\AjaxDataAccess",
            $code,
            $previous
        );
    }
}
