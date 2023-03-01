<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FormatNumberAccounting extends Enum
{
    const FORMAT_ACCOUNTING_VIETNAMESE = '_-* #,##0\ [$₫-42A]_-;\-* #,##0\ [$₫-42A]_-;_-* "-"\ [$₫-42A]_-;_-@_-';
    const FORMAT_ACCOUNTING_USD = '_([$USD]\ * #,##0_);_([$USD]\ * \(#,##0\);_([$USD]\ * "-"_);_(@_)';
    const FORMAT_ACCOUNTING_EURO = '_([$€-2]\ * #,##0_);_([$€-2]\ * \(#,##0\);_([$€-2]\ * "-"_);_(@_)';
    const FORMAT_ACCOUNTING_ENGLISH_UNITED_STATES = '_([$$-409]* #,##0_);_([$$-409]* \(#,##0\);_([$$-409]* "-"_);_(@_)';
    const FORMAT_ACCOUNTING_ENGLISH_UNITED_KINGDOM = '_-[$£-809]* #,##0_-;\-[$£-809]* #,##0_-;_-[$£-809]* "-"_-;_-@_-';
}
