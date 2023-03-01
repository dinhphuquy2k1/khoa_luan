<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Horizontal extends Enum
{
    const General = 0;
    const Left = 1;
    const HorizontalCenter = 2;
    const Right = 3;
    const Fill = 4;
    const Justify = 5;
    const CenterAcrossSelection = 6;
    const Distrubuted=7;
}
