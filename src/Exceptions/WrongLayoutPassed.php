<?php

declare(strict_types=1);

namespace Nakukryskin\OrchidRepeaterField\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class WrongLayoutPassed extends \Exception
{
    #[Pure]
    public function __construct(?string $layout = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            "Wrong or empty layout {$layout} passed. Did you connected layout correctly?",
            $code,
            $previous
        );
    }
}
