<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FormatNumber extends Enum
{
    const Number =   0;
    const Currency =   1;
    const Accounting = 2;
    const Date = 3;
    const Time = 4;
    const Text = 5;
}
