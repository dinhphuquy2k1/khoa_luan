<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\FormulaType;
class Node {

    public $info;
    public $left;
    public $right;
    public $type;

    public function __construct($info) {
           $this->info = $info;
           $this->left = NULL;
           $this->right = NULL;
           $this->type = NULL;
    }

    public function __toString() {
           return "$this->info";
    }
}

class TreeFormula extends Model
{
    use HasFactory;
    public $root;

    public $arrMul = [];
    public $formula = "";



    public function  __construct() {
        $this->root = NULL;
    }


    /**
     * Nếu một ký tự là toán tử, hãy bật hai giá trị từ ngăn xếp, biến chúng thành con của nó và đẩy lại nút hiện tại.
     * Nếu một ký tự là một toán hạng, hãy đẩy ký tự đó vào ngăn xếp
     * Cuối cùng, phần tử duy nhất của ngăn xếp sẽ là gốc của cây biểu thức.
     * **/
    public function create($arr) {
        $stack = [];
        $j = 0;


        //trường hợp có số mũ âm
        foreach($arr as $key=>$value)
        {
            // dd($value);
            if($arr[$key]['type']=='Unary Operator'){
                if($arr[$key]['value'] == '~'){
                    $arr[$key-1]['value']  = '-'.$arr[$key-1]['value'];
                }
                else if($arr[$key]['value'] == '%'){
                    $arr[$key-1]['value']  = $arr[$key-1]['value'].'%';
                }

                unset($arr[$key]);
            }
        }

        foreach($arr as $key=>$value){
            $this->root =  new Node($arr[$key]['value']);
            $this->root->type = FormulaType::Other;
            if($arr[$key]['type']  === FormulaType::BinaryOperator){
                $t1 = array_pop($stack);
                $t2 = array_pop($stack);
                $this->root->left = $t2;
                $this->root->right = $t1;
                $this->root->type = FormulaType::BinaryOperator;
            }

            array_push($stack,$this->root);
        }
        $this->root = array_pop($stack);
        return $this->root;
    }

    public function showFormula(){
        $this->formula = "";
        $this->show($this->root);
        return $this->formula;
    }



    public function compareFormula($formula1,$formula2) {

    }

    /**
     * lấy ra số hạng đầu trong công thức
     *  @param string $formula
     *  @param int $position Vị trí bắt đầu lấy
     *  @return array Return firstTerm and flag
     * **/
    private function getFirstTerm($string,$position){
        $result = [];

        $arr = [];
        $flag = false;
        if($string[$position-1] == ')'){
            $dem = 0;
            for ($i=$position - 1; $i>=0;$i--) {
                array_push($arr,$string[$i]);
                if($string[$i]==')'){
                    $dem++;
                }
                else if($string[$i]== '('){
                    $dem--;
                    if($dem == 0) break;
                }
            }
        }
        else{
            for ($i=$position-1; $i>=0;$i--) {
                if(!in_array($string[$i],['*','/','+','-','(','='])){

                    array_push($arr,$string[$i]);
                }
                else
                {
                    if($string[$i] == '('){
                        $flag = true;
                    }
                    break;}
            }
        }
        $arr = array_reverse($arr);

        $firstTerm = join('',$arr);
        array_push($result,$firstTerm,$flag);
        return $result;
    }

    /**
     * lấy ra số hạng cuối trong công thức
     *  @param string $formula
     *  @param int $position Vị trí bắt đầu lấy
     *  @return string Return lastTerm
     * **/
    private function getLastTerm($string,$position){
        $arr = [];
        //tìm số bị chia
        //sau phép chia là biểu thức
        if($string[$position+1] == '('){
            $dem = 0;
            for ($i=$position+1; $i < strlen($string);$i++) {
                array_push($arr,$string[$i]);
                if($string[$i]=='('){
                    $dem++;
                }
                else if($string[$i]== ')'){
                    $dem--;
                    if($dem == 0) break;
                }
            }
        }
        //sau phép chia là địa chỉ
        else{
            for ($i=$position+1; $i <strlen($string) ; $i++) {
                if(!in_array($string[$i],['*','/','+','-','(',')'])){
                    array_push($arr,$string[$i]);
                }
                else break;
            }
        }

        return join('',$arr);
    }
    /**
     * Convert các phép trừ thành phép cộng
     * trong công thức
     * **/
    private function convertSub($string,$position = null){
        // (A-(B+C))
        // $string='A-(B+((C^-2)-D)))';
        if($position){
            $position = $position;
        }
        else{
            $position = strrpos($string, '-');
        }

        $search = '';    //phép chia cần thay thế
        $replace = '';   //phép chia thay thế
        $flag = false;
        //chuỗi có phép trừ
        //chuỗi có phép chia
        if($position){
            if($string[$position-1]!='^'){   //E.g   (A*B^-1)-C
                $arr = [];

                $firstTerm = $this->getFirstTerm($string,$position);
                $flag  = $firstTerm[1];
                $firstTerm = $firstTerm[0];
                //số bị trừ
                $lastTerm = $this->getLastTerm($string,$position);
                if($flag){
                    $search = '('.$firstTerm.'-'.$lastTerm.')';
                }
                else{
                    $search = $firstTerm.'-'.$lastTerm;
                }


                //trường hợp sau phép trừ là nhiều biểu thức (E.g: (A-(B+C+D)))
                if(substr_count($lastTerm,'(') > 1){
                    $lastTerm = $this->convertSubLastTerm($lastTerm);
                    $replace = $firstTerm.'::'.$lastTerm;
                    $string = str_replace($search,$replace,$string);
                }
                else{
                    if(str_contains($lastTerm,'^')){
                        $replace = $firstTerm.'::'.substr($lastTerm,1,strlen($lastTerm)-2);
                        $replace = str_replace('--','+',$replace); //đổi 2 dấu -- liên tục thành cộng
                        $string = str_replace($search,$replace,$string);
                    }
                    //trừ cho phép nhân (Eg. (A-(B*C))
                    else if(str_contains($lastTerm,'*')){
                        $replace = $firstTerm.'::'.substr($lastTerm,1,strlen($lastTerm)-2);
                        $replace = str_replace('--','+',$replace); //đổi 2 dấu -- liên tục thành cộng
                        $string = str_replace($search,$replace,$string);
                    }
                    else if(str_contains($lastTerm,'-')){
                        dd(6);
                    }
                    //trừ cho 1 biểu thức cộng  (Eg: (A-(B+C)))
                    else if(str_contains($lastTerm,'+')){
                        $replace = substr($lastTerm,1,strlen($lastTerm)-2);
                        $replace = $firstTerm.'::'.str_replace('+','::',$replace);
                        $string = str_replace($search,$replace,$string);
                    }
                    //trường hợp còn lại : trừ cho địa chỉ (Eg: ((A-B)-C)
                    else{
                        $replace = $firstTerm.'::'.$lastTerm;
                        $replace = str_replace('--','+',$replace); //đổi 2 dấu -- liên tục thành cộng
                        $string = str_replace($search,$replace,$string);
                    }

                    //tiếp tục convert các dấu trừ phía sau
                    $position = strrpos($string, '-');
                    while($string[$position-1] == '^'){
                        $position = strrpos(substr($string,0,$position),'-');
                    }
                    if($position){
                        $string = $this->convertSub($string,$position);
                    }
                }
            }
            else{
                $position = strpos($string,'-',0);
                if($position){
                    while($string[$position-1]=='^'){
                        $position = strpos($string,'-',++$position);
                    }
                    if($position){
                        $string = $this->convertSub($string,$position);
                    }
                }

            }
        }
        $string = str_replace('::','+-',$string);
        // dd($string);
         return $string;
    }

    /**
     * Loại bỏ ngoặc ở các phép tính
     * **/
    private function convertSubLastTerm($string){
        // dd($string);
        // $string = '((C+D)+(E*F))';
        //flag để đánh dấu các trường hợp sau đổi dấu
        $start = strpos($string,'(');
        $end = strrpos($string,')');
        $search = substr($string,$start,$end-$start+1);
        $replace = substr($search,1,$end-$start-1);
        $position = 0;
        $operator = '';

        $flag = 0;
        if(substr($replace,-1) != ')'){            //e.g:   (B+C)-D
            $position = strrpos($replace,')')+1;
            $flag = 0;
        }
        else if($replace[0] != '('){               //e.g:    D+(B+C)
            $position = strpos($replace,'(')-1;
            $flag = 1;
        }
        else{                                      //e.g:    (B+C)*(C+D)
            $position = strpos($replace,')')+1;
            $flag = 2;
        }


        if($replace[$position]== '-'){
            if($flag ==0){    //e.g: (B+C)-D
                $replace = substr_replace($replace,'+',$position,1);   //đổi dấu -D thành +D
                $firstTerm =$this->getFirstTerm($replace,$position); //tiếp tục convert phần (B+C)
                $convert = $this->convertSubLastTerm($firstTerm[0]);
                $replace = str_replace($firstTerm[0],$convert,$replace);
            }
        }
        else if($replace[$position]== '+'){
            $replace = str_replace(array( '(', ')' ), '', $replace);  //xóa các dấu (,) trong chuỗi
            $replace = str_replace('+','::',$replace);
            $replace = str_replace('-','+',$replace);
        }
        else if($replace[$position]== '*'){
            if($flag == 0){

            }
            else if($flag==1){
                $lastTerm = $this->getLastTerm($replace,$position);
                if(str_contains($lastTerm,'^')){                               //E.g: C*(D^2)
                    $replace = str_replace(array( '(', ')' ), '', $replace);  //xóa các dấu (,) trong chuỗi
                }
            }
            else if($flag==2){

            }
            // $replace =substr_replace($replace,'*-1*',$position,1);
        }

        $string = str_replace($search,$replace,$string);
        return $string;

    }


    /**
     * Thực hiện chuyển đổi các phép chia
     * trong công thức thành phép nhân
     * **/
    private function convertDivison($string){
        $position = strpos($string, '/');
        $search = '';    //phép chia cần thay thế
        $replace = '';   //phép chia thay thế
        $flag = false;
        //chuỗi có phép chia
        if($position){
            $arr = [];
            //tìm số chia
            //trước phép chia là 1 biểu thức khác ví dụ (A+B)/C

            //số chia
            $firstTerm = $this->getFirstTerm($string,$position)[0];

            //số bị chia
            $lastTerm = $this->getLastTerm($string,$position);

            $search = $firstTerm.'/'.$lastTerm;  //phép chia cần thay thế

            //kiểm tra các trường hợp số bị chia chứa biểu thức (Eg: A/(B*C) or A/(B+C))
            //trường hợp chia cho số mũ
            if(str_contains($lastTerm,'^')){
                $replace = substr($lastTerm,1,strlen($lastTerm)-2);
                //số mũ
                $exponential = explode('^',$replace)[1];
                if(is_numeric($exponential)){
                    (int)$exponential *= -1;
                }
                else{
                    $exponential = '-'.$exponential;
                }
                //cơ số
                $number = explode('^',$replace)[0];
                $replace = $firstTerm.'*'.$number.'^'.$exponential;
                $string = str_replace($search,$replace,$string);
            }
            //chia cho phép nhân
            else if(str_contains($lastTerm,'*')){
                $replace = substr($lastTerm,1,strlen($lastTerm)-1);  //A/(B*C)  =>xóa dấu ngoặc ( ở (B*C)
                $replace = str_replace('*','^-1*',$replace);  //đỏi phép nhân trong B*C thành B^-1*C
                $replace = str_replace(')','^-1',$replace);
                $replace = $firstTerm.'*'.$replace;
                $string = str_replace($search,$replace,$string);
            }
            //chia cho phép trừ
            else{
                $replace = $firstTerm.'*'.$lastTerm.'^-1';
                $string = str_replace($search,$replace,$string);
            }
            //kiểm tra xem còn dấu chia trong chuỗi ko
            if(str_contains($string,'/')){
                $string = $this->convertDivison($string);
            }

        }
        return $string;
    }


    private function setFormula($node){
        if($node){
            if($node->type ===FormulaType::BinaryOperator){
                // echo '(';
                $this->formula .= '(';
            }
            $this->setFormula($node->left);

            // echo $node;
            $this->formula .= $node;
            $this->setFormula($node->right);
            if($node->type ===FormulaType::BinaryOperator){
                // echo ')';
                $this->formula .=')';
            }
        }
    }
    public function getFormula(){
        $this->formula = "";
        $this->setFormula($this->root);
        return $this->formula;
    }

    function showTree(){
        return $this->root;
    }

    public function test($node){
        if($node){
            if($node->info === '*' && $node->left->type === FormulaType::BinaryOperator){
               $this->getLeft($node->left);
            }
            $this->test($node->left);
        }
    }

    public function getLeft($node){
        if($node){
            $this->getLeft($node->left);
            $this->arrMul[]['ts1'] = $node->info;
            $this->getLeft($node->right);
        }
    }


    public function show(){
        $this->test($this->root);
        dd($this->arrMul);
    }

}
