<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PageOrientation extends Enum
{
    const Portrait = 0;
    const Landscape  = 1;
}
