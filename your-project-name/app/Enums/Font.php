<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Font extends Enum
{
    const CambriaHeadings =   0;
    const CambriaBody =   1;
    const Arial = 'Arial';
    const TimeNewRoman = 'Times New Roman';
}
