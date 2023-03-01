<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class TypeProperty extends Enum
{

    /**
     * Format font
     * **/
    const Font =   0;
    const FontStyle =   1;
    const FontSize = 2;
    const FontUnderline = 3;
    const FontColor =  4;
    const FontEffect =   5;
    /**
     * format Alignment
     * **/
    const Horizontal = 7;
    const Vertical = 8;
    const Indent = 9;
    const TextControl = 10;
    const TextDirection = 11;
    const AlignOrientation = 12;

    /**
     * format table
     * **/

     const TableName = 13;
     const TableStyle = 14;
     const TableStyleOptions = 15;

     /**Page Layout**/
     const PageOrientation = 20;
     const PageSize = 21;


     const AdvancedFilter = 22;
     const Text = 23;
     const Formula = 24;


     const RowHeight = 25;
     const ColumnWidth = 26;

     const SheetName = 27;

     const FormatNumber = 28;

     const FileName = 29;

     const FontSizeAll = 30;
     const FontAll = 31;
}
