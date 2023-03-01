<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PageSize extends Enum
{
    const Letter = 0;
    const Tabloid = 1;
    const Legal = 2;
    const Statement = 3;
    const Executive = 4;
    const A3 = 5;
    const A4 = 6;
    const A5 = 7;
    const B4 = 8;
    const B5 = 9;
}
