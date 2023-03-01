<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Vertical extends Enum
{
    const Top = 0;
    const Center = 1;
    const Bottom = 2;
    const Justify = 3;
    const Distrubuted = 4;
}
