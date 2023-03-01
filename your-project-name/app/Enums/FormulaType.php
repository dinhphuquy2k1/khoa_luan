<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FormulaType extends Enum
{
    const CellReference = 'Cell Reference'; // địa chỉ
    const StructuredReference = 'Structured Reference'; //vị trí tham chiếu nằm trong bảng
    const BinaryOperator = 'Binary Operator';
    const Function = 'Function';
    const UnaryOperator = 'Unary Operator';
    const OperanCountIf = 'Operand Count for Function IF()';
    const Other = 'Other'; // dùng khi là toán hạng
    const Formula = 1; //là công thức
    const Value = 3; //trường hợp điền số vào
    const Addition = 4; //phép cộng
    const Subtraction = 5; //phép trừ
    const Multiplication = 6; //phép nhân
    const Division = 7; //phép chia
    const More = 8; //lớn hơn
    const Less = 9; //Nhỏ hơn
    const Equal = 10; //Bằng
    const Ampersand = 11; //dấu &
    const COUNTIF = 13;
    const SUM = 14;
    const MAX = 15;
    const MIN = 16;
    const AVERAGE = 17;
    const SUMIF = 18;
    const RANK = 19;
    const IF = 20;
    const EXPRESSION = 21;
}
