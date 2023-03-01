<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TextDirection extends Enum
{
    const Context = 0;
    const LeftToRight = 1;
    const RightToLeft = 2;
}
