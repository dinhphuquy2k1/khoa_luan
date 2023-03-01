<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TextControl extends Enum
{
    const WrapText = 0;
    const ShrinkToFit = 1;
    const MergeCells = 2;
}
