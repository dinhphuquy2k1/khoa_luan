<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FontStyle extends Enum
{
    const Regular =   0;
    const Italic =   1;
    const Bold = 2;
    const BoldItalic = 3;
}
