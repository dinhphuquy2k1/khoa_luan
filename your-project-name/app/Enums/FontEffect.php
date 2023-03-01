<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FontEffect extends Enum
{
    const Strikethrough =   0;
    const Superscript =   1;
    const Subscript = 2;
}
