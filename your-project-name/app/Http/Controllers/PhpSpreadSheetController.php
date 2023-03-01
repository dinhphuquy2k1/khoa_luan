<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Calculation\BinaryComparison;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Conditional;
use PhpOffice\PhpSpreadsheet\Calculation\Logical;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\Operands\StructuredReference;
use PhpOffice\PhpSpreadsheet\Calculation\FormulaParser;
use PhpOffice\PhpSpreadsheet\Calculation\FormulaToken;
use App\Models\tieu_chi_test;
use App\Models\ki_thi;
use App\Models\excel_exam_bank;
use App\Models\ca_thi;
use App\Models\de_thi;
use App\Models\Criteria;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter;
use App\Models\TreeFormula;
use PHPUnit\Framework\TestCase;
use App\Enums\FormulaType;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

/**enum**/
use App\Enums\TypeProperty;
use App\Enums\TextControl;
use App\Enums\FontColor;
use App\Enums\Font;
use App\Enums\Vertical;
use App\Enums\FormatNumberAccounting;
use App\Enums\FormatNumber;
class PhpSpreadSheetController extends Controller
{
    protected $spreadsheet;
    protected $list = [];
    protected $sheetCount = 0;  //số lượng sheet trong bài thi
    private const BINARY_OPERATORS = [
        '+' => true, '-' => true, '*' => true, '/' => true,
        '^' => true, '&' => true, '>' => true, '<' => true,
        '=' => true, '>=' => true, '<=' => true, '<>' => true,
        '∩' => true, '∪' => true, ':' => true,
    ];

    public function saveData(){
        $test = tieu_chi_test::insert(request()->all());
    }

    public function listExam(){
        $list =  DB::table('ki_thi')->paginate(15);
        return $list;
    }

    public function uploadFileExam(Request $request){
        $file =  $request->file('file');
        // dd($request->file('file'));
        // // $request->all('file')["file"]
        // $image = $request->all('file')["file"];
        $file->move(public_path().'/assets/products', 'test');
    }

    /**
     * Tạo đề thi mẫu
     * **/
    public function create_excel_bank(Request $request){
        $attributes = request()-> validate([
            'excel_bank_name'=> 'required | min:3 | max: 100',
            'mo_ta' => '',
        ]);

        $id = excel_exam_bank::insertGetId($attributes);

        return $id;
    }

    /**
     * Giải nén thư mục , tách đề
     * **/
    public function extractExam(){
        $zip = new ZipArchive;
        $res = $zip->open('assets/501/[22-23-2] COMP 103-Nộp bài thi P501-114498.zip');
        if ($res === TRUE) {
            // Extract file
            $zip->extractTo('assets/501/Exam');
            $zip->close();
          } else {

            echo '<br><font color=red><b>failed!</b></font>';
          }

        $path = 'assets/501';
        $dirParent = $path.'/Exam';
        $handleParent = opendir($dirParent);
        $valid_xlsx = array('xlsx');
        $valid_docx = array('docx');
        $valid_pptx = array('pptx');

        $pathTo = "E";
        is_dir($path.'/Copy/E') ?: mkdir($path.'/Copy/E',0777,true);  //tạo các thư mục lưu trữ bài
        is_dir($path.'/Copy/P') ?: mkdir($path.'/Copy/P',0777,true);
        is_dir($path.'/Copy/W') ?: mkdir($path.'/Copy/W',0777,true);
        $writer = new Xlsx($this->spreadsheet);

        $students = [];
        if($handleParent){
            //đọc folder bài tải lên
            $key = 1;
            while (($fileParent = readdir($handleParent)) !== FALSE) {
                if (!in_array($fileParent, array('.', '..')) && !is_dir($dirParent.$fileParent)){
                    //đọc từng folder con để lấy ra bài word | excel | pp tương ứng
                    $dirChild = $dirParent.'/'.$fileParent;

                    $parts = explode('_',$fileParent);
                    $studentID = $parts[0];                                             //mã sinh viên
                    $studentName = trim(substr($parts[1],0,strpos($parts[1],'-')));     //tên sinh viên

                    $infos = explode(' ',explode('-',$parts[1])[1]);

                    $key_student =  $infos[0];                                          //Khóa
                    $studentDepartment = $infos[1];                                     //Khoa

                    //thông tin sinh viên
                    $students[$studentID] = ['studentID'=> $studentID,'studentName'=>$studentName,'studentOrder'=>$key,'key_student'=>$key_student, 'studentDepartment'=>$studentDepartment];
                    if ($handleChild = opendir($dirChild)) {
                        while (($fileChild = readdir($handleChild)) !== FALSE) {

                            if (!in_array($fileChild, array('.', '..')) && !is_dir($dirChild.$fileChild)){
                                // if($studentID === '705103089'){
                                //     dd($fileChild);
                                // }
                                $ext = strtolower(pathinfo($fileChild, PATHINFO_EXTENSION));
                                if($pathTo === "E"){
                                    if(in_array(strtolower($ext),$valid_xlsx)){

                                        $students[$studentID]['studentAssignment'][] = $fileChild;  //tên file
                                        $students[$studentID]['path'] =  $dirParent.'/'.$fileParent;  //đường dẫn đến bài
                                        // // copy($dirChild.'/'.$fileChild,$path.'/Copy/E/'.($key).'-'.$fileChild);
                                    }
                                }
                                if($pathTo === "P"){
                                    if(in_array(strtolower($ext),$valid_pptx)){
                                        copy($dirChild.'/'.$fileChild,$path.'/Copy/P/'.($key).'-'.$fileChild);
                                    }
                                }
                                if($pathTo === "W"){
                                    if(in_array(strtolower($ext),$valid_pptx)){
                                        copy($dirChild.'/'.$fileChild,$path.'/Copy/W/'.($key).'-'.$fileChild);
                                    }
                                }
                            }
                        }
                        closedir($handleChild);
                    }
                    $key++;
                }
            }
            closedir($handleParent);
        }
        $this->spreadsheet = IOFactory::load($path.'/Danh sách phòng thi.xlsx');
        $this->spreadsheet->setActiveSheetIndex(0);


        is_dir($path.'/Copy/E/de3') ?: mkdir($path.'/Copy/E/de3',0777,true);
        is_dir($path.'/Copy/E/de4') ?: mkdir($path.'/Copy/E/de4',0777,true);
        // is_dir($path.'/Copy/P') ?: mkdir($path.'/Copy/P',0777,true);
        // is_dir($path.'/Copy/W') ?: mkdir($path.'/Copy/W',0777,true);
        for ($i= 2; $i <= $this->spreadsheet->getActiveSheet()->getHighestRow() ; $i++) {
            $exam_number_list[] = [(int)$this->spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue(),(int)$this->spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue()];  //danh sách số báo danh, mã sinh viên

            $studentID = (int)$this->spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue();    //mã sinh viên
            $keyStudent = (int)$this->spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue();   //số báo danh

            //kiểm tra mã trong mảng bài thi đã nộp
            if(array_key_exists($studentID,$students) && isset($students[$studentID]['path'])){
                $dir = $students[$studentID]['path'];
                $handle = opendir($dir);
                while (($file = readdir($handle)) !== FALSE) {
                    //đọc folder của ng thi
                    foreach ($students[$studentID]['studentAssignment'] as $key => $itemFile) {
                        if($file === $itemFile){
                            if($keyStudent %2 ===0){
                                $this->spreadsheet->getActiveSheet()->getCell('O'.$i)->setValue('de4');
                                $this->spreadsheet->getActiveSheet()->getCell('P'.$i)->setValue($studentID.'-'.$students[$studentID]['studentAssignment'][$key]);
                                copy($students[$studentID]['path'].'/'.$itemFile,$path.'/Copy/E/de4/'.$studentID.'-'.$students[$studentID]['studentAssignment'][$key]);

                            }
                            else{
                                $this->spreadsheet->getActiveSheet()->getCell('O'.$i)->setValue('de3');
                                $this->spreadsheet->getActiveSheet()->getCell('P'.$i)->setValue($studentID.'-'.$students[$studentID]['studentAssignment'][$key]);
                                copy($students[$studentID]['path'].'/'.$itemFile,$path.'/Copy/E/de3/'.$studentID.'-'.$students[$studentID]['studentAssignment'][$key]);
                            }
                        }
                    }
                }
            }
        }

        $file = IOFactory::createWriter($this->spreadsheet,'Xlsx');
        $file->save($path.'/test15b.xlsx');

        // dd($students,$exam_number_list);

    }

    /**
     * Lấy danh sách đề trong ngân hàng đề
     * **/
    public function get_excel_bank(){
        $list =  DB::table('excel_exam_bank')->paginate(15);
        return $list;
    }

    /**
     * Lấy ra danh sách đề thi theo mã ca thi
     * **/
    public function getExamByExamSessionId(Request $request){
        $list =  DB::table('excel_exam_bank')->join('chitietcathi','chitietcathi.id_de_thi','=','excel_exam_bank.id')->join('ca_thi','ca_thi.id','=','chitietcathi.id_ca_thi')->where('ca_thi.id',$request->id)->get();
        return $list;
    }

    /**
     * Lưu chi tiết ca thi
     * **/
    public function create_detail_exam_session(Request $request){
        DB::table('chitietcathi')->insert($request->all());
    }

    /**
     * Lấy danh sách các ca thi dựa vào id kì thi
     * **/
    public function getExamSessionInfos($id_ki_thi){
        $examSessionInfo =  DB::table('ca_thi')->where('id_ki_thi',$id_ki_thi)->get();
        return $examSessionInfo;
    }
    public function upload(Request $request){
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            // file not uploaded
        }

        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            // dd($fileReceived);
            // $file->move(public_path().'/assets/products', 'test');
            $new_name = rand() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path().'/assets/'.$request->id,$file->getClientOriginalName());
            // $fileName .= '_' . md5(time()) . '.' . $extension; // a unique file name

            // $disk = Storage::disk(config('filesystems.default'));
            // $path = $disk->putFileAs('videos', $file, $fileName);

            // // delete chunked file
            // unlink($file->getPathname());
            // return [
            //     'path' => asset('storage/' . $path),
            //     'filename' => $fileName
            // ];
        }

    }

    public function getAssessmentInfos(){
        $assessmentInfos = DB::table('excel_exam_bank')->paginate(15);
        return $assessmentInfos;
    }

    /**
     * Tạo ca thi
     * **/
    public function create_exam_session(Request $request){
        ca_thi::insert($request->all());
    }

    public function saveExam(Request $request){
        $id_ki_thi = ki_thi::insertGetId($request[0]);
    }

    public function mark(){
        $tieu_chi = DB::table('tieu_chi_test')->where('id_de_thi',1)->get();
        $dir = "assets/501/Copy/E/de3";
        // $this->readFile();
        if ($handle = opendir($dir)) {
            //duyệt từng file trong thư mục exam
            while (($file = readdir($handle)) !== false){
                if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)){
                    //đọc file excel
                    $this->spreadsheet = IOFactory::load($dir.'/'.$file,IReader::LOAD_WITH_CHARTS);
                    $this->spreadsheet->setActiveSheetIndex(0);
                    $arrName = [];  //mảng lưu name range
                    // dd($this->spreadsheet->getSheet(1)->getCell('731!F32'));
                    foreach ($this->spreadsheet->getDefinedNames() as $key => $value) {
                        //bỏ qua các vùng lọc
                        if(str_contains($key,'XLNM.CRITERIA') || str_contains($key,'XLNM.EXTRACT')){
                            $title = $value->getWorksheet()->getTitle();

                            $objName['subName'] = $value->getName();
                            $objName['range']= str_replace('$','',$value->getValue());
                            $objName['name'] = $key;
                            $objName['sheet_index'] =$this->spreadsheet->getIndex($value->getWorksheet());
                            array_push($arrName,$objName);
                        }
                    }
                    // foreach ($this->spreadsheet->getSheet(0)->getChartCollection() as $key => $chart) {
                    //     // dd($chart);
                    // }
                    //lấy ra các table có trong bài thi
                    $arrTable = [];
                    $sheets = [];
                    $this->sheetCount = $this->spreadsheet->getSheetCount();
                    for ($i=0; $i <$this->sheetCount ; $i++) {
                        //lấy ra thông tin về sheet
                        $objSheet['SheetName'] = $this->spreadsheet->getSheet($i)->getTitle();
                        $objSheet['SheetIndex'] = $i;
                        array_push($sheets,$objSheet);

                        //lấy ra thông tin các table trong sheet
                        foreach ($this->spreadsheet->getSheet($i)->getTableCollection() as $table) {
                        //    dd($table->getColumnByOffset(1));
                            $objTable['TableName'] = $table->getName();
                            $objTable['TableRange'] = $table->getRange();
                            $objTable['SheetIndex'] = $i;
                            [$rangeStart, $rangeEnd] = Coordinate::rangeBoundaries($table->getRange());
                            $objTable['RangeStart'] = $rangeStart[1];
                            $objTable['RangeEnd'] = $rangeEnd[1];
                            $header = [];
                            //duyệt toàn bộ header của bảng để lấy thông tin
                            for ($col=$rangeStart[0]; $col <=$rangeEnd[0] ; $col++) {
                                //duyệt header để lấy thông tin
                                $headerTable = new class{};
                                $cellHeader = $this->spreadsheet->getSheet($i)->getCell([$col,$rangeStart[1]]); //lấy ra cell header
                                $headerTable->Field = $cellHeader->getValue();
                                $headerTable->ColumnIndex = $col;
                                $headerTable->ColumnName = $cellHeader->getColumn();
                                $header[$cellHeader->getValue()] = $headerTable;
                                // $headerTable[$cell->getValue()]= $header;
                                // array_push($header,$headerTable);
                            }
                            $objTable['Column'] = $header;
                            $arrTable[$table->getName()]=$objTable;
                        }


                        // $chartCollection = [];
                        // foreach ($this->spreadsheet->getSheet(0)->getChartCollection() as $key => $chart) {
                        //     // $chartCollection[$chart->getName()]
                        //     dd($chart->getPlotArea());
                        // }
                    }




                    //khởi tạo mảng lưu thông tin về các tiêu chí của 1 bài thi
                    $criteriaList = [];

                    //class lưu thông tin tiêu chí mỗi lần duyệt
                    $criteria = [
                        'name'=>'',
                        'point'=>0,
                        'flag' =>true,
                    ];

                    $totalScore = 0;
                    //duyệt từng tiêu chí trong db
                    foreach($tieu_chi as $key=>$itemCriteria){
                        $criteria['point'] = $itemCriteria->point;
                        $criteria['name'] = $itemCriteria->property_name;
                        $criteria['flag'] = true;
                        switch($itemCriteria->type_property){
                            case TypeProperty::Font:
                                break;
                            case TypeProperty::FontStyle:
                                if(!$this->FontStyle($itemCriteria->id_sheet,$itemCriteria->add_start,$itemCriteria->content)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::FontSize:
                                break;
                            case TypeProperty::FontUnderline:
                                break;
                            case TypeProperty::FontColor:
                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }
                                //tiêu đề sheet
                                $title = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTitle();
                                //vùng cần kiểm tra color
                                $arrRange = Coordinate::extractAllCellReferencesInRange($title.'!'.$itemCriteria->add_start);
                                $COLOR = FontColor::getValue($itemCriteria->content);
                                $COLOR = 1;
                                $worksheet = $this->spreadsheet->getSheet($itemCriteria->id_sheet);
                                foreach ($arrRange as $key => $range) {
                                    $color = $worksheet->getCell($range)->getStyle()->getFont()->getColor()->getArgb();

                                    if($color != $COLOR){
                                        $criteria['flag'] = false;
                                        $criteria['point'] = 0;
                                        break;
                                    }
                                }
                                break;
                            case TypeProperty::FontEffect:
                                break;
                            case TypeProperty::Horizontal:
                                $func = \App\Enums\Horizontal::getKey((int)$itemCriteria->content);
                                if($this->$func($itemCriteria->id_sheet,$itemCriteria->add_start,$itemCriteria->add_end)){
                                    $criteria['flag'] = true;
                                }
                                else{
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::Vertical:
                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }

                                //tiêu đề sheet
                                $title = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTitle();
                                //vùng cần kiểm tra color
                                $arrRange = Coordinate::extractAllCellReferencesInRange($title.'!'.$itemCriteria->add_start);

                                foreach ($arrRange as $key => $range) {
                                    switch ($itemCriteria->content) {
                                        case Vertical::Top:

                                            break;
                                        case Vertical::Center:

                                            break;
                                        case Vertical::Bottom:

                                            break;
                                        case Vertical::Justify:

                                            break;
                                        case Vertical::Distrubuted:

                                            break;
                                        default:
                                            # code...
                                            break;
                                    }
                                }

                                break;
                            case TypeProperty::Indent:
                                break;
                            case TypeProperty::TextControl:
                                $func = TextControl::getKey((int)$itemCriteria->content);
                                if($this->$func($itemCriteria->add_start,$itemCriteria->add_end)){
                                    $criteria['flag'] = true;
                                }
                                else{
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::TextDirection:
                                break;
                            case TypeProperty::RowHeight:

                                break;
                            case TypeProperty::FileName:
                                if($file !== $itemCriteria->content){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::ColumnWidth:
                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }

                                $arrColumn = explode(',',$itemCriteria->add_start);

                                foreach ($arrColumn as $key => $column) {
                                    $columnWidth = round($this->spreadsheet->getSheet($itemCriteria->id_sheet)->getColumnDimension($column)->getWidth()-0.71,2);
                                    if($itemCriteria->content != $columnWidth){
                                        $criteria['flag'] = false;
                                        $criteria['point'] = 0;
                                        break;
                                    }
                                }
                                break;
                            case TypeProperty::TableName:
                                if(!$this->TableName($itemCriteria->id_sheet,$itemCriteria->add_start,$itemCriteria->add_end,$itemCriteria->content,$arrTable)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }

                                break;
                            case TypeProperty::TableStyle:
                                //vùng table
                                $range = $itemCriteria->add_start.':'.$itemCriteria->add_end;
                                //tìm kiếm trong mảng table đã đọc từ bài thi
                                $table = array_filter($arrTable,function($table) use($range){
                                    return $table['TableRange'] === $range;
                                });
                                if($table){
                                    if($this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTableByName(end($table)['TableName'])->getStyle()->getTheme() !==\App\Enums\TableStyles::getKey((int)$itemCriteria->content)){
                                        $criteria['flag'] = false;
                                        $criteria['point'] = 0;
                                    }
                                }
                                else{
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::PageSize:
                                if(!$this->PageSize($itemCriteria->id_sheet,$itemCriteria->content)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }
                                break;
                            case TypeProperty::PageOrientation:
                                $func = TypeProperty::getKey($itemCriteria->type_property);
                                // $criteria['name'] = \App\Enums\PageOrientation::getKey((int)$itemCriteria->content);
                                if($this->$func($itemCriteria->id_sheet,$itemCriteria->content)){
                                    $criteria['flag'] = true;
                                }
                                else{
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::Text:
                                if($this->sheetExists($itemCriteria->id_sheet)){
                                    if($this->spreadsheet->getSheet($itemCriteria->id_sheet)->getCell($itemCriteria->add_start)->getValue() !== $itemCriteria->content){
                                        $criteria['flag'] = false;
                                        $criteria['point'] = 0;
                                    }
                                }
                                else{
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::AlignOrientation:
                                break;
                            case TypeProperty::FormatNumber:
                                $content = json_decode($itemCriteria->content);
                                switch ($content->type) {
                                    case FormatNumber::Number:
                                        # code...
                                        break;
                                    case FormatNumber::Currency:
                                        break;
                                    case FormatNumber::Accounting:
                                        //lấy ra giá trị value tương ứng với tên enum
                                        $FORMAT_ACCOUNTING = FormatNumberAccounting::getValue($content->symbol);
                                        //thêm số lượng 0.00 tùy theo decimal
                                        $FORMAT_ACCOUNTING = str_replace("#0",'#'.number_format(0,$content->decimal),$FORMAT_ACCOUNTING);
                                        //nếu sheet ko tồn tại thì tiêu chí false
                                        if(!$this->sheetExists($itemCriteria->id_sheet)){
                                            $criteria['flag'] = false;
                                            $criteria['point'] = 0;
                                            break;
                                        }
                                        $title = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTitle();
                                        $arrRange =  Coordinate::extractAllCellReferencesInRange($title.'!'.$itemCriteria->add_start);
                                        //kiểm tra vùng
                                        $worksheet = $this->spreadsheet->getSheet($itemCriteria->id_sheet);
                                        foreach ($arrRange as $key => $range) {
                                            $formatCode = $worksheet->getCell($range)->getStyle()->getNumberFormat()->getFormatCode();
                                            if($formatCode !== $FORMAT_ACCOUNTING){
                                                $criteria['flag'] = false;
                                                $criteria['point'] = 0;
                                                break;
                                            }
                                        }
                                        break;
                                    case FormatNumber::Date:
                                        break;
                                    case FormatNumber::Time:
                                        break;
                                    case FormatNumber::Text:
                                        break;
                                    default:
                                        # code...
                                        break;
                                }

                                break;
                            case TypeProperty::FontSizeAll:
                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }

                                if($this->spreadsheet->getSheet(0)->getStyle('A1:XFD1048576')->getFont()->getSize() != $itemCriteria->content){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }
                                break;
                            case TypeProperty::FontAll:

                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }

                                if($this->spreadsheet->getSheet(0)->getStyle('A1:XFD1048576')->getFont()->getName() !==Font::getValue($itemCriteria->content)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                    break;
                                }
                                break;
                            case TypeProperty::Formula:
                                // break;
                                $content = json_decode($itemCriteria->content);
                                $type_formula = (int)$content[0];
                                $content = $content[1];

                                $criteria['name'] = FormulaType::getKey((int)$type_formula);

                                $calculation = Calculation::getInstance($this->spreadsheet);

                                $criteria['point'] = $itemCriteria->point;  //điểm tiêu chí

                                //vùng cần check công thức
                                $titleSheet = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTitle();//tiêu đề sheet
                                $rangeFormulaBranch = Coordinate::extractAllCellReferencesInRange($titleSheet.'!'.$itemCriteria->add_start);

                                $formulaMaster = $this->convertFormulaMaster($content,$type_formula,$arrTable,$rangeFormulaBranch[0]);
                                //các trường hợp ko tồn tại sheet
                                $criteria['flag'] = $formulaMaster[0];
                                $calculatedValueFormulaMaster = $formulaMaster[2];   //giá trị công thức



                                //trường hợp sheet tồn tại
                                if($criteria['flag']){
                                      //duyệt vùng các công thức cần kiểm tra
                                    foreach ($rangeFormulaBranch as $key => $range) {
                                        //cell cần kiểm tra
                                        $cell = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getCell($range);

                                        //kiểm tra có phải công thức ko
                                        if(!$cell->isFormula()){
                                            $criteria['flag'] = false;
                                            $criteria['point'] = 0;
                                            break;
                                        }
                                        else{
                                            if($type_formula !== FormulaType::EXPRESSION){
                                                $needle = FormulaType::getKey((int)$type_formula);
                                                //kiểm tra xem có chứa đúng hàm hay ko
                                                if(stripos($cell->getValue(),$needle) === false){
                                                    $criteria['flag'] = false;
                                                    $criteria['point'] = 0;
                                                    break;
                                                }
                                            }
                                            $formulaBranch = $this->convertArgumentFormula($cell,$arrTable,$itemCriteria->id_sheet,$type_formula,substr($range,strpos($range,'!')+1));
                                            // //kiểm tra value
                                            $calculatedValueFormulaBranch = $calculation->calculateFormula($formulaBranch[0]);
                                            //kiểm tra value công thức
                                            if($calculatedValueFormulaMaster !== $calculatedValueFormulaBranch){
                                                $criteria['flag'] = false;
                                                $criteria['point'] = 0;
                                                break;
                                            }

                                            switch ($type_formula) {
                                                case FormulaType::RANK:
                                                    $formulaBranch[0] = str_replace("'",'',$formulaBranch[0]);
                                                    if(!BinaryComparison::compare($formulaBranch[0],$formulaMaster[1],'=')){
                                                        $criteria['flag'] = false;
                                                        $criteria['point'] = 0;
                                                        break;
                                                    }
                                                    //cập nhật lại công thức gốc
                                                    $firstTerm = $this->getFirstTerm($formulaMaster[1],strpos($formulaMaster[1],','));
                                                    $search = substr($firstTerm,strpos($firstTerm,'!')+1);
                                                    $replace =  Coordinate::indexesFromString($search);
                                                    $replace = $replace[2].$replace[1]+1;
                                                    $replace = $titleSheet.'!'.$replace;
                                                    $formulaMaster[1] = str_replace($firstTerm,$replace,$formulaMaster[1]);
                                                    break;
                                                case FormulaType::MAX:
                                                case FormulaType::MIN:
                                                case FormulaType::AVERAGE:
                                                    $formulaBranch[0] = str_replace("'",'',$formulaBranch[0]);
                                                    $formulaMaster[1] = str_replace("'",'',$formulaMaster[1]);
                                                    if(!BinaryComparison::compare($formulaBranch[0],$formulaMaster[1],'=')){
                                                        $criteria['flag'] = false;
                                                        $criteria['point'] = 0;
                                                        break;
                                                    }
                                                    break;
                                                case FormulaType::IF:
                                                    $arrMaster = $formulaMaster[3];
                                                    $arrBranch = $formulaBranch[3];

                                                    foreach ($arrMaster as $keyM => $itemMaster) {
                                                        $flag = false;
                                                        foreach ($arrBranch as $keyB => $itemBranch) {
                                                            if($itemMaster['valueIfTrue'] && $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] || $itemBranch['valueIfFalse'] ===$itemMaster['valueIfTrue']){
                                                                //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                                                                $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfTrue'] ? true : false;

                                                                $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],false);

                                                            }
                                                            else if($itemMaster['valueIfFalse'] && $itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] || $itemBranch['valueIfFalse'] ===$itemMaster['valueIfFalse']){
                                                                //nếu valueB trùng ở nhánh false sẽ phải so sánh biểu thức nhánh với đối của biểu thức gốc
                                                                $valueB = $itemBranch['valueIfTrue'] === $itemMaster['valueIfFalse'] ? true : false;

                                                                $valueB ? $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],true) :  $flag = $this->checkIfCondition($itemMaster['Logic'],$itemBranch['Logic'],false);
                                                            }
                                                             //nếu tìm thấy và biểu thức bằng nhau thì dừng
                                                             if($flag){
                                                                break;
                                                            }

                                                        }
                                                        if(!$flag){
                                                            $criteria['flag'] = false;
                                                            $criteria['point'] = 0;
                                                            break;
                                                        }

                                                        // dd($formulaMaster);
                                                        $formulaMaster = $this->updateFomulaMaster($formulaMaster,$type_formula);
                                                        $calculatedValueFormulaMaster = $calculation->calculateFormula($formulaMaster[1]);
                                                    }
                                                    break;
                                                case FormulaType::EXPRESSION:
                                                    // dd($formulaBranch,$formulaMaster);
                                                    if(!$this->array_equal($formulaBranch[1]['*'],$formulaMaster[1][1]['*'])){
                                                        $criteria['flag'] = false;
                                                        $criteria['point'] = 0;
                                                        break;
                                                    }
                                                    else if(!$this->array_equal($formulaMaster[1][1]['+'],$formulaBranch[1]['+'])){
                                                        $criteria['flag'] = false;
                                                        $criteria['point'] = 0;
                                                        break;
                                                    }
                                                    else if(!$this->array_equal($formulaMaster[1][1]['^'],$formulaBranch[1]['^'])){
                                                        $criteria['flag'] = false;
                                                        $criteria['point'] = 0;
                                                        break;
                                                    }
                                                    //cập nhật lại công thức gốc
                                                    $formulaMaster = $this->updateFomulaMaster($formulaMaster,$type_formula);
                                                    //tính toán lại giá trị công thức gốc
                                                    $calculatedValueFormulaMaster = $calculation->calculateFormula($formulaMaster[1][0]);
                                                    break;
                                                default:
                                                    # code...
                                                    break;
                                            }
                                        }

                                        // if($criteria['flag']){
                                        //     switch ($type_formula) {
                                        //         case FormulaType::COUNTIF:
                                        //         case FormulaType::SUMIF:
                                        //             if(!BinaryComparison::compare($formulaBranch,$formulaMaster,'=')){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //             }
                                        //             break;
                                        //         case FormulaType::SUM:
                                        //             dd(5);
                                        //             break;
                                        //         case FormulaType::MAX:
                                        //         case FormulaType::MIN:
                                        //         case FormulaType::AVERAGE:
                                        //             if(!BinaryComparison::compare(trim($formulaBranch),trim($formulaMaster),'=')){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //             }
                                        //             break;
                                        //         case FormulaType::RANK:
                                        //             if(!BinaryComparison::compare($formulaBranch[0],$formulaMaster,'=')){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //                 break;
                                        //             }
                                        //             //cập nhật lại công thức gốc
                                        //             $firstTerm = $this->getFirstTerm($formulaMaster,strpos($formulaMaster,','));
                                        //             $search = substr($firstTerm,strpos($firstTerm,'!')+1);
                                        //             $replace =  Coordinate::indexesFromString($search);
                                        //             $replace = $replace[2].$replace[1]+1;
                                        //             $replace = $titleSheet.'!'.$replace;
                                        //             $formulaMaster = str_replace($firstTerm,$replace,$formulaMaster);
                                        //             break;
                                        //         case FormulaType::IF:
                                        //             $isReverse = false;  //reserve mảng kết quả nếu gặp biểu thức đối
                                        //             // dd($arrGoc,$arrTest);
                                        //             foreach($formulaBranch[1] as $itemBranch){
                                        //                 if($itemBranch['type'] ===  FormulaType::BinaryOperator){
                                        //                     $result = $this->checkIfCondition($itemBranch,$formulaMaster[1]);
                                        //                     $criteria['flag'] = $result[0];
                                        //                     $isReverse = $result[1] ? $result[1] : $isReverse;
                                        //                     if(!$criteria['flag']){
                                        //                         break;
                                        //                     }
                                        //                 }
                                        //             }
                                        //             if($criteria['flag']){
                                        //                 if($isReverse){
                                        //                     $formulaBranch[2] = array_reverse($formulaBranch[2]);
                                        //                 }
                                        //                 if($formulaMaster[2] !== $formulaBranch[2]){
                                        //                     $criteria['flag'] = false;
                                        //                     break;
                                        //                 }
                                        //             }
                                        //             //cập nhật lại công thức gốc
                                        //             $formulaMaster = $this->updateFomulaMaster($formulaMaster,$type_formula);
                                        //             //tính toán công thức gốc
                                        //             $calculatedValueFormulaMaster = $calculation->calculateFormula($formulaMaster[0]);

                                        //             break;
                                        //         case FormulaType::EXPRESSION:

                                        //             if(!$this->array_equal($formulaBranch[1]['*'],$formulaMaster[1]['*'])){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //                 break;
                                        //             }
                                        //             else if(!$this->array_equal($formulaMaster[1]['+'],$formulaBranch[1]['+'])){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //                 break;
                                        //             }
                                        //             else if(!$this->array_equal($formulaMaster[1]['^'],$formulaBranch[1]['^'])){
                                        //                 $criteria['flag'] = false;
                                        //                 $criteria['point'] = 0;
                                        //                 break;
                                        //             }

                                        //             //cập nhật lại công thức gốc
                                        //             $formulaMaster = $this->updateFomulaMaster($formulaMaster,$type_formula);
                                        //             $calculatedValueFormulaMaster = $calculation->calculateFormula($formulaMaster[0]);
                                        //             break;
                                        //         default:
                                        //             # code...
                                        //             break;
                                        //     }
                                        // }

                                        if(!$criteria['flag']){
                                            break;
                                        }
                                    }
                                }

                                break;
                            case TypeProperty::SheetName:
                                if(!$this->sheetName($itemCriteria->id_sheet,$itemCriteria->content)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                break;
                            case TypeProperty::AdvancedFilter:
                                $criteria['name'] = 'AdvancedFilter';
                                $content = json_decode($itemCriteria->content)[0];
                                //sheet ko tồn tại
                                if(!$this->sheetExists($itemCriteria->id_sheet)){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                $title = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getTitle();
                                //vùng tiêu chí advanced
                                $criteriaRange = $title."!".$content->add_start.':'.$content->add_end;
                                //tìm vùng tiêu chí trong file bài thi
                                $isCriteriaRange = array_filter($arrName,function($defineName) use ($criteriaRange){
                                    return str_replace("'",'',$defineName['range'])=== $criteriaRange && $defineName['subName'] == '_xlnm.Criteria';
                                });
                                //nếu ko có
                                if(!$isCriteriaRange){
                                    $criteria['flag'] = false;
                                    $criteria['point'] = 0;
                                }
                                else{
                                    //kiểm tra vùng trả dữ liệu
                                    $data_reference = json_decode($itemCriteria->data_reference);
                                    $rangeDataMaster[0] = [$data_reference->add_start,$data_reference->add_end];
                                    $rangeDataMaster[0] = Coordinate::buildRange($rangeDataMaster);
                                    $rangeDataMaster[1] = Coordinate::splitRange($rangeDataMaster[0])[0][0];  //ô dữ liệu đầu tiên E.g:A4
                                    $rangeDataMaster[0] = Coordinate::rangeDimension($rangeDataMaster[0])[0]; //chiều dài vùng dữ liệu
                                    $rangeDataMaster[2] = $data_reference->id_sheet;
                                    foreach ($arrName as $key => $defineName) {
                                        if(str_contains($defineName['range'],$title) && $defineName['subName'] == '_xlnm.Extract'){

                                            $rangeDataTest = explode('!',$defineName['range']);
                                            $rangeDataTest[0] = end($rangeDataTest);
                                            $rangeDataTest[1] =  Coordinate::splitRange($rangeDataTest[0])[0][0];
                                            $rangeDataTest[0] = Coordinate::rangeDimension($rangeDataTest[0])[0];
                                            if($rangeDataMaster[0] !== $rangeDataTest[0]){
                                                $criteria['flag'] = false;
                                                $criteria['point'] = 0;
                                                break;
                                            }

                                            //kiểm tra value của ô đầu tiên của vùng trả dữ liệu
                                            $valueCellMaster = $this->spreadsheet->getSheet($data_reference->id_sheet)->getCell($rangeDataMaster[1])->getValue();
                                            $valueCellTest = $this->spreadsheet->getSheet($itemCriteria->id_sheet)->getCell($rangeDataTest[1])->getValue();
                                            if($valueCellMaster !== $valueCellTest){
                                                $criteria['flag'] = false;
                                                $criteria['point'] = 0;
                                                break;
                                            }

                                            break;
                                        }
                                    }

                                }
                                break;

                        }
                        $totalScore += $criteria['point'];
                        array_push($criteriaList,(object)[...$criteria]);
                    }
                    // dd($criteriaList);
                    $this->list[substr($file,0,9)] = [$criteriaList,$totalScore];
                    // array_push($this->list,[$criteriaList,$totalScore,]);
                }
                //   dd($criteriaList);
            }
            // dd($this->list);
            $this->spreadsheet = IOFactory::load('assets/501/test15b.xlsx');
            $this->spreadsheet->setActiveSheetIndex(0);

            for ($i= 5; $i <= $this->spreadsheet->getActiveSheet()->getHighestRow() ; $i++) {
                $studentID = (int)$this->spreadsheet->getActiveSheet()->getCell('B'.$i)->getValue();    //mã sinh viên
                $keyStudent = (int)$this->spreadsheet->getActiveSheet()->getCell('A'.$i)->getValue();   //số báo danh
                if(array_key_exists($studentID,$this->list)){
                    $this->spreadsheet->getActiveSheet()->getCell('Q'.$i)->setValue($this->list[$studentID][1]);
                }
            }


            $file = IOFactory::createWriter($this->spreadsheet,'Xlsx');
            $file->save('assets/501/test15b.xlsx');

        }
    }

    /**
     * Convert địa chỉ các tham số của công thức
     * về dạng tên sheet!address  (E.g: sheet1!A1)
     * @param string $formula công thức cần convert
     * @param array $arrTable Mảng table đọc được từ bài thi
     * @param int   $sheetIndex sheet lấy dữ liệu dùng cho công thức
     * @param int   $type Loại công thức (SUM,COUNT...)
     * @param string    $cellAddress vị trí cell đặt công thức
     * @return string|array $formula đã được convert về dạng tương ứng
     * **/
    function convertArgumentFormula($formula,$arrTable,$sheetIndex,$type,$cellAddress){
        $title = $this->spreadsheet->getSheet($sheetIndex)->getTitle();  //tiêu đề sheet
        switch ($type) {
            case FormulaType::COUNTIF:
            case FormulaType::SUMIF:
                preg_match('/\((.*)\)/', $formula, $matches);
                $search = $matches[1];
                $replace = $matches[1];             //lấy ra thông tin công thức trong cặp dấu () E.g: =COUNTIF(Sheet1!E3:E17,">=5") => Sheet1!E3:E17,">=5"
                $replace = explode(',',$replace);   //tách các đối số
                foreach ($replace as $key => $item) {
                    if($key === 1){             //điều kiện
                        $replace[$key] = preg_replace('/(?<![<>])=/', '', $replace[$key]);  //xóa dấu = nếu trước dấu = ko phải dấu < or >
                    }
                    else{
                        if(str_contains($item,'[')){
                            $replace[$key] = $this->parseStructuredReference($replace[$key],$arrTable,$cellAddress);
                        }
                        else{
                            $replace[$key] = $this->spreadsheet->getSheet($sheetIndex)->getTitle().'!'.$replace[$key];
                        }
                    }
                }
                $replace = join(",",$replace);
                $formula = str_replace($search,$replace,$formula);  //công thức gốc sau convert
                return [$formula];
            case FormulaType::MAX:
            case FormulaType::MIN:
            case FormulaType::AVERAGE:
                //lấy ra các đối số trong hàm
                $arguments = $this->getArgumentsFormula($formula);

                foreach ($arguments as $key => $arg) {
                    if(str_contains($arg,'[')){
                        $replace = $this->parseStructuredReference($arg,$arrTable,$cellAddress);
                        $range =  Coordinate::extractAllCellReferencesInRange($replace);
                        $replace = join(',',$range);
                        $formula =str_replace($arg,$replace,$formula);
                    }
                    else if(str_contains($arg,':')){
                        if(str_contains($arg,'!')){
                            $range = Coordinate::extractAllCellReferencesInRange($arg);
                        }
                        else{
                            $range = Coordinate::extractAllCellReferencesInRange($title.'!'.$arg);
                        }
                        $replace = join(',',$range);
                        $formula = str_replace($arg,$replace,$formula);
                    }
                }
                return [$formula];
                break;
            case FormulaType::SUM:
                preg_match_all('/SUM\([^)]+\)/', $formula, $matches);       //lấy ra các công thức sum (nếu có nhiều e.g: =SUM(E5:G5)-SUM(E6:G6) => [SUM(E5:G5) , SUM(E6:G6)])
                $arrSum = $matches[0];
                foreach ($arrSum as $key => $item) {
                    preg_match('/\((.*)\)/', $item, $matches);          //lấy ra các đối số trong công thức E.g: =SUM(E5:G5)   => E5:G5
                    $arguments = $matches[1];

                    $arguments = explode(',',$arguments);   //tách các đối số để kiểm tra xem đối số nào thuộc các dạng ko phải mặc định thì thực hiện convert(E.g: Table1[Số ngày])
                    foreach ($arguments as $key => $arg) {
                        if(str_contains($arg,'[')){
                            $arguments[$key] = $this->parseStructuredReference($arg,$arrTable,$cellAddress);
                        }
                    }
                    $arguments = join('+',$arguments);

                    $arguments = Coordinate::extractAllCellReferencesInRange($arguments);  //E5:G5 => [E5,G5,F5]

                    $arguments = join('+',$arguments);
                    $formula = str_replace($item,'('.$arguments.')',$formula);
                }
                break;
            case FormulaType::RANK:
                preg_match('/\((.*)\)/', $formula, $matches);
                $search = $matches[1];
                //lấy ra các đối số trong hàm
                $arguments = $this->getArgumentsFormula($formula);

                $lastTerm =  array_pop($arguments);     //xóa phần tử cuối(giá trị cuối chỉ có 0 hoặc 1)
                foreach ($arguments as $key => $arg) {
                    //dạng struc
                    if(str_contains($arg,'[')){
                        $arguments[$key] = $this->parseStructuredReference($arg,$arrTable,$cellAddress);

                        if(str_contains($arguments[$key],':')){
                            $explodedRange = Coordinate::splitRange($arguments[$key]);  //tách nếu đang là vùng
                            foreach ($explodedRange[0] as $k=>$item) {
                                $explodedRange[0][$k] = Coordinate::absoluteReference($item);
                            }
                            $arguments[$key] =Coordinate::buildRange($explodedRange);   //địa chỉ tuyệt đối
                        }
                    }
                    else if(!str_contains($arg,'!')){
                        $arguments[$key] = $title.'!'.$arg;
                    }
                }
                // dd($arguments);
                array_push($arguments,$lastTerm);               //đẩy lại giá trị cuối vào mảng
                $arguments = join(',',$arguments);
                $formula = str_replace($search,$arguments,$formula);
                return [$formula];
            case FormulaType::IF:
                $formula = $this->parseIf($formula->getValue(),$arrTable,$cellAddress,false);
                return $formula;
                break;
            case FormulaType::EXPRESSION:

                $calculation = Calculation::getInstance($this->spreadsheet);

                //convert
                $parseFormula = $calculation->parseFormula($formula);

                foreach ($parseFormula as $key => $item) {
                    if($item['type'] === FormulaType::CellReference && !str_contains($item['value'],'!')){
                        $formula = str_replace($item['value'],$title.'!'.$item['value'],$formula);
                    }
                    else if($item['type'] === FormulaType::StructuredReference){
                        $address = $this->parseStructuredReference($item['value']->value(),$arrTable,$cellAddress); //địa chỉ đã được convert
                        $formula = str_replace($item['value']->value(),$address,$formula);
                    }
                }
                $resultFormula = $formula;

                $findArgsGoc = $this->parseExpression($formula);
                return [$resultFormula,$findArgsGoc];
            break;
            default:
                # code...
                break;
        }

        return $formula;
    }

    /**
     * Tách nhỏ công thức
     * **/
    function parseExpression($formula){
        //tìm các biểu thức dạng lũy thừa E.g: (A+B*D)^2
        $argsExpo = $this->findExponents($formula);
        $formula = $argsExpo[0];
        //mảng chứa các phép lũy thừa của hàm gốc
        $argsExpo = $argsExpo[1];

        //convert phép tính theo thứ tự /,*,-
        $formula = $this->convertDivison($formula);
        $formula = $this->convertMultiplication($formula);
        $formula = $this->convertSub($formula);
        $formula = str_replace(['(', ')','='],'',$formula);

        $argsGoc = explode('+',$formula);

        $argsMultiplication = []; //mảng chứa các phần tử của phép nhân
        $argsSum = []; //mảng chứa các số hạng của phép cộng

        for ($i=0; $i <count($argsGoc) ; $i++) {
            if(str_contains($argsGoc[$i],'*')){
                array_push($argsMultiplication, ...explode('*',$argsGoc[$i]));
            }
            else{
                array_push($argsSum,$argsGoc[$i]);
            }
        }
        $findArgsGoc = $this->findAndMove($argsMultiplication,$argsSum,$argsExpo);
        return $findArgsGoc;
    }

    /**
     * Cập nhật lại công thức gốc cho tiêu chí
     * E.g: Sheet1!F7 => Sheet1!F8
     * @param string|array $formula Công thức cần cập nhật
     * @param int $type_formula Loại công thức cập nhật
     * **/
    function updateFomulaMaster($formula,$type_formula){
        switch ($type_formula) {
            case FormulaType::IF:
                foreach ($formula[4] as $key => $value) {
                    //địa chỉ cần thay thế
                    $search = $value[0];

                    //địa chỉ cập nhật
                    $formula[4][$key][3] +=1;
                    $replace = $value[1].'!'.$value[2].$formula[4][$key][3];
                    //cập nhật
                    foreach ($formula[3] as $keyF => $itemF) {
                        $logic = explode('::',str_replace($search,$replace,join('::',$itemF['Logic'])));
                        $formula[3][$keyF]['Logic'] = $logic;
                        $formula[3][$keyF]['valueIfTrue'] = str_replace($search,$replace,$formula[3][$keyF]['valueIfTrue']);
                        $formula[3][$keyF]['valueIfFalse'] = str_replace($search,$replace,$formula[3][$keyF]['valueIfFalse']);
                    }
                }

                break;
            case FormulaType::EXPRESSION:
                foreach ($formula[1][1] as $key1 => $formulaData) {

                    foreach ($formulaData as $key2 => $item) {
                        if(!is_numeric($item)){
                            $address = explode('!',$item);
                            $coordinateFromString = Coordinate::coordinateFromString(array_pop($address));
                            $coordinateFromString[1] += 1;
                            $coordinateFromString = join('',$coordinateFromString);
                            $address[] = $coordinateFromString;
                            $formula[1][1][$key1][$key2] = join('!',$address); //cập nhật địa chỉ
                            //cập nhật công thức
                            $formula[1][0] = str_replace($item, $formula[1][1][$key1][$key2],$formula[1][0]);
                        }
                    }
                }
                break;
            default:
                # code...
                break;
        }

        return $formula;
    }

    /**
     * Convert công thức gốc
     * @param string $formula Công thức cần convert
     * @param int $type Loại công thức cần convert
     * @return array [false nếu sheet ko tồn tại trong bài thi,công thức, giá trị công thức]
     * **/
    function convertFormulaMaster($formula,$type_formula,$arrTable,$cellAddress){

        switch ($type_formula) {
            case FormulaType::COUNTIF:
                preg_match('/\((.*)\)/', $formula, $matches);
                $search = $matches[1];

                $replace = $matches[1];
                $replace = explode('!',$replace);
                if($this->sheetExists((int)$replace[0])){
                    $calculation = Calculation::getInstance($this->spreadsheet);
                    $title = $this->spreadsheet->getSheet((int)$replace[1])->getTitle();
                    $replace = $title.'!'.$replace[1];
                    $formula = str_replace($search,$replace,$formula);
                    //tính giá trị công thức
                    $calculateFormula = $calculation->calculateFormula($formula);
                    return [true,$formula,$calculateFormula];
                }

                return [false,$formula];
                break;
            case FormulaType::RANK:
                preg_match('/\((.*)\)/', $formula, $matches);   //=RANK(1!E3,1!E3:E16,0) lấy ra các đối số E.g: 1!E3,1!E3:E16,0

                $parts = explode(',',$matches[1]); //tách nhỏ
                $end = array_pop($parts); //tách giá trị cuối cùng
                $calculation = Calculation::getInstance($this->spreadsheet);
                $isSheetExists = false;  //sheet ko tồn tại
                foreach ($parts as $key => $arg) {
                    $replace = explode('!',$arg);
                    //kiểm tra sheet có tồn tại
                    if($this->sheetExists((int)$replace[0])){
                        $title = $this->spreadsheet->getSheet((int)$replace[0])->getTitle();
                        $replace = $title.'!'.$replace[1];
                        $formula = str_replace($arg,$replace,$formula);
                    }
                    else {
                        $isSheetExists = true;
                        break;
                    };
                }

                if($isSheetExists){
                    return [false,$formula,0];
                }
                  //tính giá trị công thức
                $calculateFormula = $calculation->calculateFormula($formula);
                return [true,$formula,$calculateFormula];
                break;
            case FormulaType::MAX:
            case FormulaType::MIN:
            case FormulaType::AVERAGE:

                preg_match('/\((.*)\)/', $formula, $matches);
                $arguments = explode(',',$matches[1]);
                foreach ($arguments as $key => $arg) {
                    $parts = explode('!',$arg);
                    if($this->sheetExists((int)$parts[0])){
                        $title = $this->spreadsheet->getSheet((int)$parts[0])->getTitle();
                        $range =  Coordinate::extractAllCellReferencesInRange($title.'!'.$parts[1]);
                        $replace = join(',',$range);
                        $formula = str_replace($arg,$replace,$formula);
                    }
                    else return [false,$formula,0];

                }

                $calculation = Calculation::getInstance($this->spreadsheet);

                $calculateFormula = $calculation->calculateFormula($formula);

                return [true,$formula,$calculateFormula];
                break;
            case FormulaType::IF:
                $formula = $this->parseIf($formula,$arrTable,$cellAddress,true);
                return $formula;
                break;
            case FormulaType::EXPRESSION:
                $calculation = Calculation::getInstance($this->spreadsheet);
                $parseFormula = $calculation->parseFormula($formula);
                foreach ($parseFormula as $key => $arg) {
                    if($arg['type'] != FormulaType::BinaryOperator && str_contains($arg['value'],'!')){
                        $parts = explode('!',$arg['value']);
                        if(!$this->sheetExists((int)$parts[0])){
                            return [false,$formula,0];
                        }
                        $title = $this->spreadsheet->getSheet((int)$parts[0])->getTitle();
                        $replace = "'".$title."'!".$parts[1];
                        $formula = str_replace($arg['value'],$replace,$formula);
                    }
                }

                try {
                    $calculateFormula = $calculation->calculateFormula($formula);
                } catch (\Throwable $th) {
                    return [false,$formula,0];
                }

                $findArgsGoc = $this->parseExpression($formula);
                $formula = [$formula,$findArgsGoc];
                return [true,$formula,$calculateFormula];
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Lấy ra các đối số của hàm if
     * @param string $formula Công thức cần lấy đối số
     * @return array [logic,valueifTrue,valueifFalse]
     * **/
    function getIfArgument($formula){
        preg_match('/\((.*)\)/', $formula, $matches);
        $formula = $matches[1];
        $buffer = '';
        $stack = array();
        $depth = 0;
        $len = strlen($formula);
        for ($i=0; $i<$len; $i++) {
            $char = $formula[$i];
            switch ($char) {
            case '(':
                $depth++;
                break;
            case ',':
                if (!$depth) {
                    if ($buffer !== '') {
                        $stack[] = $buffer;
                        $buffer = '';
                    }
                    continue 2;
                }
                break;
            case ' ':
                if (!$depth) {
                    continue 2;
                }
                break;
            case ')':
                if ($depth) {
                    $depth--;
                } else {
                    $stack[] = $buffer.$char;
                    $buffer = '';
                    continue 2;
                }
                break;
            }
            $buffer .= $char;
        }
        if ($buffer !== '') {
            $stack[] = $buffer;
        }
        return $stack;
    }

    function parseIfLogic($logic){
        dd($logic);
        // preg_match('/\((.*)\)/', $logic, $matches);
        $arr = [];
        // while (preg_match('/\((.*)\)/', $logic, $matches)) {
        //     # code...
        // }
        dd($matches);

    }

    /**
     *  Tách hàm if
     *  @param string $formula Công thức
     *  @param int $index Vị trí thêm phần tử
     *  @return array $result Mảng các phần tử gồm [logic,valueiftrue,valueiffalse]
     *  nếu có nhiều if lồng thì [[logic,valueiftrue,valueiffalse],[logic,valueiftrue,valueiffalse]]
     * **/
    function splitIfStatement($formula,$result,$index){
        $parts = $this->getIfArgument($formula);
        $result[$index]['Logic'][] = $parts[0];

        // dd($this->parseIfLogic($parts[0]));

        // if(str_contains($parts[0],'(') && preg_match('/\((.*)\)/', $parts[0], $matches)){
        //     preg_match('/\((.*)\)/', $parts[0], $matches);
        // }
        if(stripos($parts[1],'IF') === false){
            $result[$index]['valueIfTrue'] = $parts[1];
        }
        else{
            $result[$index]['valueIfTrue'] = null;
            $result = $this->splitIfStatement($parts[1],$result,$index+1);
        }
        if(stripos($parts[2],'IF') === false){
            $result[$index]['valueIfFalse'] = $parts[2];
        }
        else{
            $result[$index]['valueIfFalse'] = null;
            $result = $this->splitIfStatement($parts[2],$result,$index+1);
        }
        return $result;
    }

    /**
     * convert công thức if
     * @param string $formula công thức
     * @param array $arrTable Các table đọc được từ bài thi
     * @param string $cellAddress địa chỉ ô cần tính
     * @param bool $type true: công thức gốc, false công thức bài thi
     * @return array [công thức,giá trị true&false,biểu thức logic,true|false cho công thức gốc,giá trị công thức]
     * **/
    function parseIf($formula,$arrTable,$cellAddress,$type){
        // $formula = $this->spreadsheet->getSheet(0)->getCell('G3')->getValue();

        // $formulaParser = new FormulaParser($this->spreadsheet->getSheet(0)->getCell('G3')->getValue());
        // dd($formulaParser->getTokens());

        $calculation = Calculation::getInstance($this->spreadsheet);
        $parseFormula = $calculation->parseFormula($formula);


        $arrArgument = [];
        foreach($parseFormula as $key=>$tokenData){
            //convert địa chỉ
            if($tokenData['type'] === FormulaType::StructuredReference){
                $search = $tokenData['value']->value();
                $parseFormula[$key]['value'] = $this->parseStructuredReference($tokenData['value']->value(),$arrTable,$cellAddress);
                $formula = str_replace($search,$parseFormula[$key]['value'],$formula);    //thay thế

            }
            //convert địa chỉ
            if(!is_numeric($parseFormula[$key]['value']) && $parseFormula[$key]['type'] === FormulaType::CellReference){
                //convert địa chỉ
                $parseIfArgument = $this->parseIfArguments($formula,$parseFormula[$key],$arrTable,$cellAddress,$type);
                if($type){
                    if(!$parseIfArgument[0]){
                        return [$formula,$values,$logics,false,0];
                    }
                    else{
                        $formula = $parseIfArgument[1];
                        $parseFormula[$key] = $parseIfArgument[2];
                    }
                }
                //công thức bài thi
                else{
                    $formula = $parseIfArgument[0];
                    $parseFormula[$key] = $parseIfArgument[1];
                }
            }


            if($parseFormula[$key]['type'] === FormulaType::CellReference){
                $parts = explode('!',$parseFormula[$key]['value']);
                array_splice($parts,0,0,$parseFormula[$key]['value']);
                array_push($parts,...Coordinate::coordinateFromString(array_pop($parts)));
                $arrArgument[] = $parts;
            }
        }


        // $formula = '=IF(B3="Nam Định",IF(E3>5,100000,0),0)';
        $result = [];
        $index = 0;
        $splitIfStatement =  $this->splitIfStatement($formula,$result,$index);

        $values = [];
        //xử lý giá trị trùng
        foreach ($splitIfStatement as $key => $valueIF) {
            //tìm kiếm giá trị false trùng nhau
            $findDuplicateFalse =  array_filter($splitIfStatement,function($obj) use($valueIF){
                return $obj['valueIfFalse'] == $valueIF['valueIfFalse'] && !is_null($obj['valueIfFalse']);
            });
            //nếu có
            if(count($findDuplicateFalse)>1){
                unset($findDuplicateFalse[0]);
                foreach ($findDuplicateFalse as $keyD => $valueD) {
                    if($splitIfStatement[$key]['Logic'][0] === 'OR'){
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);
                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                    else{
                        array_splice($splitIfStatement[$key]['Logic'],0, 0, 'OR' );
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);

                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                }
            }

            $findDuplicateTrue =  array_filter($splitIfStatement,function($obj) use($valueIF){
                return $obj['valueIfTrue'] == $valueIF['valueIfTrue'];
            });

            //kiểm tra các giá trị value if true trùng
            //nếu trùng thì thực hiện nối các biểu thức thành biểu thức and
            //e.g: AND(E3>5,E5>6)
            if(count($findDuplicateTrue)>1){
                unset($findDuplicateTrue[0]);
                foreach ($findDuplicateTrue as $keyD => $valueD) {
                    if($splitIfStatement[$key]['Logic'][0] === 'AND'){
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);
                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                    else{
                        array_splice($splitIfStatement[$key]['Logic'],0, 0, 'AND' );
                        array_push($splitIfStatement[$key]['Logic'],...$valueD['Logic']);

                        $splitIfStatement[$key]['valueIfTrue'] = $valueD['valueIfTrue'] ?? $splitIfStatement[$key]['valueIfTrue'];
                        array_splice($splitIfStatement,$keyD,1);
                    }
                }
            }

            // $values[] = ['valueIfTrue'=>$valueIF['valueIfTrue'],'valueIfFalse'=>$valueIF['valueIfFalse']];
        }





        //tính toán giá trị công thức
        try {
            $calculateFormula = $calculation->calculateFormula($formula);
        } catch (\Throwable $th) {
            return [false,$formula,0,$splitIfStatement];
        }

        if($type){
            return [true,$formula,$calculateFormula,$splitIfStatement,$arrArgument];
        }
        return [$formula,true,$calculateFormula,$splitIfStatement,$arrArgument];
        // dd($values,$logics,$parseFormula);
    }

    /**
     * Convert địa chỉ đối số hàm if
     * @param string $formula công thức
     * @return array Return arguments các đối số
     * **/
    function parseIfArguments($formula,$argument,$arrTable,$cellAddress,$type){
        if($argument['type'] === FormulaType::StructuredReference){
            $search = $argument['value']->value();
            $argument['value'] = $this->parseStructuredReference($argument['value']->value(),$arrTable,$cellAddress);
            $formula = str_replace($search, $argument['value'],$formula);
            return [$formula,$argument];
        }
        else if($argument['type'] === FormulaType::CellReference){
            //đã có tên sheet trong địa chỉ //E.g: Sheet1!A3
            if(str_contains($argument['value'],'!')){
                if($type){
                    $parts = explode('!',$argument['value']);
                    if(!$this->sheetExists((int)$parts[0])){
                        return [false,$formula,$argument]; //sheet ko tồn tại
                    }
                    $parts[0] = $this->spreadsheet->getSheet((int)$parts[0])->getTitle();
                    $replace = join('!',$parts);
                    $formula = str_replace($argument['value'],$replace,$formula);
                    $argument['value'] = $replace;
                    return [true,$formula,$argument];
                }
            }else{ //công thức nhánh
                if(!$type){
                    dd(6);
                }
            }
        }
        return [true,$formula,$argument];
    }

    /**
     * Convert địa chỉ nằm trong table về dạng cơ bản
     * @param string $structuredReference
     * @param array $arrTable Mảng table đọc được từ bài thi
     * @param string    $cellAddress địa chỉ cell lấy công thức
     * @return string $cellRange địa chỉ đã được convert E.g: Table1[Số ngày]=> sheet1!E5
     * **/
    function parseStructuredReference($structuredReference,$arrTable,$cellAddress){
        $tableName = substr($structuredReference,0,strpos($structuredReference,'['));  //lấy ra tên table
        $cell = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getCell($cellAddress);
        $cellRange = (new StructuredReference($structuredReference))->parse($cell);  //thực hiện convert
        $cellRange= $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle().'!'.$cellRange;
        return $cellRange;
    }

    /**
     * tìm kiếm các function trùng nhau trong mảng các dấu cộng,nhân,^ và đẩy
     * vào các mảng tương ứng
     *
     * **/
    function findAndMove($argsMul,$argsSum,$argsExpo){
        $countMul =  array_count_values($argsMul);
        foreach ($countMul as $key => $value) {
            if($value>1){
                $argsExpo[] = [$value,$key];
            }
        }
        $argsMul = array_keys(array_filter(array_count_values($argsMul), function($count) { return $count == 1; })); //xóa các phần tử trùng từ 2 giá trị trở lên

        $countSum = array_count_values($argsSum);
        foreach ($countSum as $key => $value) {
            if($value>1){
                array_push($argsMul,$key,$value);
            }
        }
        $argsSum = array_keys(array_filter(array_count_values($argsSum), function($count) { return $count == 1; })); //xóa các phần tử trùng từ 2 giá trị trở lên
        return ['*'=>$argsMul,'+'=>$argsSum,'^'=>$argsExpo];
    }

    function findOutermostBracket($string,$operator) {
        $bracketCount = 0;
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] == '(') {
                $bracketCount++;
            } else if ($string[$i] == ')') {
                $bracketCount--;
            } else if ($bracketCount == 1 && $string[$i] == $operator) {
                return $i;
            }
        }
        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] == '(') {
                $bracketCount++;
            } else if ($string[$i] == ')') {
                $bracketCount--;
            }
            if ($bracketCount == 0 && $string[$i] == '(') {
                return findOutermostBracket(substr($string, $i + 1, $length - $i - 2));
            }
        }
        return null;
    }

    function findOutermostBrackets($string) {
        $bracketCount = 0;
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] == '(') {
                $bracketCount++;
            } else if ($string[$i] == ')') {
                $bracketCount--;
            } else if ($bracketCount == 1 && ($string[$i] == '+' || $string[$i] == '*' || $string[$i] == '^' || $string[$i] == '-')) {
                return $i;
            }
        }
        for ($i = 0; $i < $length; $i++) {
            if ($string[$i] == '(') {
                $bracketCount++;
            } else if ($string[$i] == ')') {
                $bracketCount--;
            }
            if ($bracketCount == 0 && $string[$i] == '(') {
                return findOutermostBracket(substr($string, $i + 1, $length - $i - 2));
            }
        }
        return null;
    }

    function convert($arr){
        dd($arr);
        for ($i=count($arr) - 1; $i >=0; $i--) {
            dd($arr[$i]);
        }
    }

    /**
     *  So sánh 2 biểu thức
     *  @param array $operandMaster Mảng chứa các biểu thức gốc
     *  @param array  $operandBranch Mảng chứa các biểu thức nhánh
     *  @param bool $type false: so sánh nghịch đảo của gốc với nhánh E.g: gốc: E3>8 ,nhánh:E3<=8
     *  convert gốc về E3<=8 và so sánh, true thực hiện so sánh bình thường
     *  @return bool  true nếu đúng | false nếu sau
     * **/
    function checkIfCondition($operandMaster,$operandBranch,$type){
        $pattern = '/(<>|<=|>=|=|<|>)/';

         //tách nhỏ đối số của biểu thức nhánh
         foreach ($operandBranch as $key => $itemOperandBranch) {
            if(count(preg_split($pattern,$itemOperandBranch, -1, PREG_SPLIT_DELIM_CAPTURE))>1){
                $resultBranch[] = $this->splitCondition($itemOperandBranch);
                array_splice($operandBranch,$key,1,$resultBranch);
            }
        }
        if($type){
            //tách nhỏ đối số
            foreach ($operandMaster as $key => $itemOperandMaster) {
                if(count(preg_split($pattern,$itemOperandMaster, -1, PREG_SPLIT_DELIM_CAPTURE))>1){
                    $resultBranch[] = $this->splitCondition($itemOperandMaster);
                    array_splice($operandMaster,$key,1,$resultBranch);
                }
            }
        }
        else{
            //convert biểu thức ngược và tách nhỏ
            foreach ($operandMaster as $key => $itemOperandMaster) {
                $resultMaster[] = $this->reverseCondition($itemOperandMaster);
                array_splice($operandMaster,$key,1,$resultMaster);
            }


        }

        //so sánh biểu thức
        foreach ($operandBranch as $key => $value) {
            if(is_array($value) && $value[1] == '=' ||  $value[1] == '<>'){
                $find = array_filter($operandMaster,function($item) use($value){
                    return $this->array_equal($item,$value);
                });
            }
            else{
                $find = array_filter($operandMaster,function($item) use($value){
                    return $item === $value;
                });
            }
            if(!$find){
                return false;
            }
        }
        return true;
    }

    /**
     * Tách các đối số biểu thức điều kiện
     * @param string $condition Biểu thức cần tách
     * @return array $result Mảng các đối số E.g: E3>4=> [4,'<',E3]
     * **/
    function splitCondition($condition){
        $pattern = '/(<>|<=|>=|=|<|>)/';
        $split =  preg_split($pattern,$condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        if($split[1] === '>'){
            $result = [$split[2],'<',$split[0]];
        }
        else if($split[1] === '<'){
            $result = [$split[0],$split[1],$split[2]];
        }
        else if($split[1] === '>='){
            $result = [$split[2],'<=',$split[0]];
        }
        else if($split[1] === '<='){
            $result = [$split[0],$split[1],$split[2]];
        }
        else if($split[1] === '<>' || $split[1] === '='){
            $result = [$split[0],$split[1],$split[2]];
        }
        else $result[] = $condition;
        return $result;
    }

    /**
     * Xây dựng biểu thức đối
     * @param string $condition biểu thức cần xây dựng
     * @return string $result biểu thức đối
     * E.g: E3>4 => 4<=E3 | E3<=4 => 4<E3
     * **/
    function reverseCondition($condition){
        $pattern = '/(<>|<=|>=|=|<|>)/';
        $split =  preg_split($pattern,$condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        if($split[1] === '>'){
            $result = [$split[0],'<=',$split[2]];
        }
        else if($split[1] === '<'){
            $result = [$split[2],'<=',$split[0]];
        }
        else if($split[1] === '>='){
            $result = [$split[0],'<',$split[2]];
        }
        else if($split[1] === '<='){
            $result = [$split[2],'<',$split[0]];
        }
        else if($split[1] === '<>'){
            $result = [$split[0],'=',$split[2]];
        }
        else if($split[1] === '='){
            $result = [$split[0],'<>',$split[2]];
        }
        else $result[] = $condition;

        return $result;
    }


    /**
     * Lấy ra các đối số trong hàm (SUM,MAX,MIN...)
     * @param string $formula
     * @return array Return mảng các đối số
     * **/
    function getArgumentsFormula($formula) {
        preg_match('#\((.*?)\)#', $formula, $match);
        $formula= $match[1];
        $result = explode(",", $formula);
        foreach($result as $key=>$value){
            if(strpos($value, "[[#") !== false){
                $result[$key] .= ','.$result[$key+1];
                unset($result[$key+1]);
            }
        }
        return $result;
    }

    /**
     * so sánh biểu thức
     * @param string $operand1 biểu thức 1
     * @param string $operand2 biểu thức 2
     * @param int $type Loại so sánh (0:so sánh biểu thức bằng nhau,1: so với biểu thức đối,3:so sánh biểu thức <>,=)
     * **/
    function compareCondition($operand1,$operand2,$type = 0){
        switch ($type) {
            case 0:
                return $operand1['value']['bigNumber'] === $operand2['value']['bigNumber'] && $operand1['value']['smallNumber'] === $operand2['value']['smallNumber'];
            case 1:
                return $operand1['branch']['smallNumber'] === $operand2['value']['smallNumber'] && $operand1['branch']['bigNumber'] === $operand2['value']['bigNumber'];
            case 3:
                return $this->array_equal($operand1['value'],$operand2['value']);
        }
    }


    /**
     * E3>=8,IF(E3>=4,500,0),IF(E3>=6.5,"Khá",IF(E3>=5,"Trung Bình","Yếu"))
     *
     * **/
    function decode_IF_string($formula) {
        preg_match('/\((.*)\)/', $formula, $matches);
        $formula = $matches[1];
        $position = strpos($formula,',');
        $condition = substr($formula,0,$position);

        $formula = str_replace($condition.',','',$formula);
        $formula = 'Giỏi,IF(E3>=6.5,"Khá",IF(E3>=5,"Trung Bình","Yếu"))';
        preg_match_all("/IF\((.*?)\)/", $formula, $matches);


        // $arr = [1,2,3];
        // $arr1 = [2,4,5];
        // dd(array_unique(array_merge($arr,$arr1), SORT_REGULAR));
        dd($matches);
        if($formula[$position+1]=='I'){           //giá trị true là 1 hàm if    (Eg.)
            $position = strpos($formula,'(',$position);
            dd($this->getLastTerm($formula,$position));
        }
        dd($s,2);
    }


    /**
     * Tìm vị trí các dấu (,) trong chuỗi
     * @param string $expression Biểu thức cần tìm
     * @return array Return với 0 là vị trí các ngoặc mở, 1 là vị trí các ngoặc đóng tương ứng
     * **/
    function findAllPositionBracket($expression){
        $result = [];
        for ($i=0; $i <strlen($expression) ; $i++) {
            if($expression[$i] === '('){
                $result[0][] = $i;
            }
            else if($expression[$i] === ')'){
                $result[1][] = $i;
            }
        }
        $result[1] = array_reverse($result[1]);
        return $result;
    }

    /**
     *  Lấy ra các tham số của phép nhân
     * **/
    function extractTerms($expression){
        $exp = [];
        $index = 0;
        while ($position = strpos($expression,')^')) {
            $firstTerm = $this->getFirstTerm($expression,$position+1);
            $lastTerm = $this->getLastTerm($expression,$position+1);
            $search = $firstTerm.'^'.$lastTerm;
            $exp[] = $search;
            $expression = str_replace($search,'t'.$index,$expression);
            $index++;
        }
        $expression = str_replace(['(', ')'],'',$expression); //xóa toàn bộ dấu ngoặc đơn
        $result = explode('+',$expression);

        return [$result,$exp];
    }

    /**
     * Hàm convert phép nhân (E.g: (A+B)*C => AC+BC or (A+B)*(C+D)=> AC+AD+BC+BD)
     *
     * **/
    function convertMultiplication($formula,$position = null){
        if(!$position){
            $position =  strpos($formula,'*');
            while($position && ($formula[$position+1] !== '(' &&  $formula[$position-1] !== ')')){
                $position = strpos($formula,'*',$position+1);
            }
        }
        if($position){
            $firstTerm = $this->getFirstTerm($formula,$position);   //tham số 1
            $lastTerm = $this->getLastTerm($formula,$position);     //tham số 2
            $search = $firstTerm.'*'.$lastTerm;


            $lastTerm = str_replace('-','+-',$lastTerm);
            $firstTerm = str_replace('-','+-',$firstTerm);

            //mảng chứa các tham số vế trái
            $argsFirst = explode('+',str_replace(['(', ')'],'',$firstTerm));
            //mảng chứa các tham số vế phải
            $argsLast = explode('+',str_replace(['(', ')'],'',$lastTerm));


            //mảng chứa các tham số của tổng các tích
            $args = [];

            for ($i=0; $i <count($argsFirst) ; $i++) {
                for ($j=0; $j <count($argsLast) ; $j++) {
                    $args[] = $argsFirst[$i].'*'.$argsLast[$j];
                }
            }
            $replace = join('+',$args);
            $formula = str_replace($search,$replace,$formula);
        }

        //kiểm tra xem còn phép nhân với biểu thức nào ko (E.g: A*(C+D))
        $position =  strpos($formula,'*',$position+1);
        while($position && ($formula[$position+1] !== '(' ||  $formula[$position-1] !== ')')){
            $position = strpos($formula,'*',$position+1);
        }
        if($position){
            $formula = $this->convertMultiplication($formula,$position);
        }
        return $formula;
    }

      /**
     * Convert các phép trừ thành phép cộng
     * trong công thức
     * **/
    private function convertSub($formula,$position = null){
        if(!$position){
            $position =  strpos($formula,'-');
            while($position && ($formula[$position-1] === '*' || $formula[$position-1] === '^' ||  $formula[$position-1] === '+')){
                $position = strpos($formula,'-',$position+1);

            }
        }
        if($position){
            $firstTerm = $this->getFirstTerm($formula,$position);
            $lastTerm = $this->getLastTerm($formula,$position);
            $search =  $firstTerm.'-'.$lastTerm;
            if($formula[$position+1] === '('){                 //là biểu thức 1 (A+B)
                preg_match('/\((.*)\)/', $lastTerm, $matches);
                $replace = $matches[1];

                //tìm vị trí các dấu nằm ngoài ngoặc
                $paren_count = 0;
                $outside_paren = array();
                for ($i=0; $i<strlen($replace); $i++) {
                    if ($replace[$i] == '(') {
                        $paren_count++;
                    } elseif ($replace[$i] == ')') {
                        $paren_count--;
                    } elseif (in_array($replace[$i], array('+', '-', '*')) && $paren_count == 0) {
                        $outside_paren[] = $i;
                    }
                }

                for ($i=0; $i <count($outside_paren) ; $i++) {
                    if($replace[$outside_paren[$i]] === '-'){
                        $replace = substr_replace($replace,'+',$outside_paren[$i],1);
                    }
                    else if($replace[$outside_paren[$i]] === '+'){
                        $replace = substr_replace($replace,'-',$outside_paren[$i],1);
                    }
                }
                $formula = str_replace($lastTerm,$replace,$formula);

            }


            $formula = substr_replace($formula,'+-',$position,1);

            //tìm kiếm xem trong chuỗi còn dấu - nào ko
            $position =  strpos($formula,'-',$position+1);
            while($position && ($formula[$position-1] === '*' || $formula[$position-1] === '^' ||  $formula[$position-1] === '+')){
                $position = strpos($formula,'-',$position+1);
            }
            if($position){
                $formula = $this->convertSub($formula,$position);
            }

        }

        return $formula;

    }


    /**
     * Tìm các phép lũy thừa dạng biểu thức
     * **/
    public function findExponents($expression){
        $exp = [];
        $index = 0;
        while ($position = strpos($expression,')^')) {
            $firstTerm = $this->getFirstTerm($expression,$position+1);
            $lastTerm = $this->getLastTerm($expression,$position+1);
            $search = $firstTerm.'^'.$lastTerm;
            $exp[] = $search;
            $expression = str_replace($search,'t'.$index,$expression);
            $index++;
        }
        return [$expression,$exp];
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
            // dd($string[$position],$string);
            $dem = 0;
            for ($i=$position-1; $i>=0;$i--) {
                array_push($arr,$string[$i]);
                if(($string[$i] === '-' && $string[$i-1] !=='^')){
                    array_pop($arr);
                    break;
                }
                else if($string[$i] === ')'){
                    $dem++;
                }
                else if($string[$i] === '('){
                    $dem--;
                    if($dem <= 0){
                        array_pop($arr);
                        break;
                    }
                }
                else if(in_array($string[$i],['*','/','+','(',')','=']) && $dem == 0){
                    break;
                }

            }
        }
        $arr = array_reverse($arr);

        $firstTerm = join('',$arr);
        return $firstTerm;
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
        //sau là địa chỉ
        else{
            for ($i=$position+1; $i <strlen($string) ; $i++) {
                if(!in_array($string[$i],['*','/','+','-','(',')'])){
                    array_push($arr,$string[$i]);
                }
                else if($string[$i-1]=='^'){                        //E.g   E^-2
                    array_push($arr,$string[$i]);
                }
                else break;
            }
        }

        return join('',$arr);
    }

    /**
     * Convert vùng dữ liệu cho các công thức
     * @param string $range Vùng cần convert
     * @param array array table đọc được từ file bài thi
     * **/
    private function convertRange($range,$arrTable){
        if(str_contains($range,'[')){
            $parts = explode('[',$range);
            $tableName = $parts[0];
            if(substr_count($range,'[')>2){   //trường hợp NhaTrang[[#All],[Số ngày]]
                $tableColumn = str_replace(']', '', $parts[3]);
                //kiểm tra xem thuộc table nào
                if(array_key_exists($tableName, $arrTable)){
                    //kiểm tra xem thuộc column nào
                    if(array_key_exists($tableColumn,$arrTable[$tableName]['Column'])){
                        $title = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle();
                        $columnName = $arrTable[$tableName]['Column'][$tableColumn]->ColumnName;
                        // $range = "'".$title."'!".$columnName.$arrTable[$tableName]['RangeStart'].':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                        $range = $columnName.$arrTable[$tableName]['RangeStart'].':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                    }
                }
            }
            else{                              // NhaTrang[Số ngày]
                $tableColumn = str_replace(']', '', $parts[1]);
                if(array_key_exists($tableName, $arrTable)){
                    //kiểm tra xem thuộc column nào
                    if(array_key_exists($tableColumn,$arrTable[$tableName]['Column'])){
                        $title = $this->spreadsheet->getSheet($arrTable[$tableName]['SheetIndex'])->getTitle();
                        $columnName = $arrTable[$tableName]['Column'][$tableColumn]->ColumnName;
                        // $range = $title.'!'.$columnName.($arrTable[$tableName]['RangeStart']+1).':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                        $range = $columnName.($arrTable[$tableName]['RangeStart']+1).':'.$columnName.$arrTable[$tableName]['RangeEnd'];
                    }
                }
            }
        }
        return $range;
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
            $firstTerm = $this->getFirstTerm($string,$position);

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

    function array_equal($a, $b) {
        return (
             is_array($a)
             && is_array($b)
             && count($a) == count($b)
             && array_diff($a, $b) === array_diff($b, $a)
        );
    }


    /**
     * Kiểm tra merge cell
     * **/
    public function MergeCells($startCell,$endCell){
        if(array_key_exists(trim(strtoupper($startCell)).':'.trim(strtoupper($endCell)),$this->spreadsheet->getActiveSheet()->getMergeCells())){
            return true;
        }
        else return false;
    }

    /**
     *  Kiểm tra định dạng font chữ
     *  @param int $id_sheet vị trí sheet cần định dạng
     *  @param string $range vùng cần định dạng
     *  @param int $type loại định dạng
     *  @return bool true | faslse
     * **/
    public function FontStyle($id_sheet,$range,$type){
        if($this->sheetExists($id_sheet)){
            $ranges = Coordinate::extractAllCellReferencesInRange($range);
            $worksheet = $this->spreadsheet->getSheet($id_sheet);
            switch ($type) {
                case \App\Enums\FontStyle::Regular:

                    break;
                case \App\Enums\FontStyle::Italic:
                    foreach ($ranges as $key => $range) {
                        if(!$worksheet->getCell($range)->getStyle()->getFont()->getItalic()){
                            return false;
                        }
                    }
                    break;
                case \App\Enums\FontStyle::Bold:
                    foreach ($ranges as $key => $range) {
                        if(!$worksheet->getCell($range)->getStyle()->getFont()->getSharedComponent()->getBold()){
                            return false;
                        }
                    }
                    break;
                case \App\Enums\FontStyle::BoldItalic:
                    break;
                default:
                    # code...
                    break;
            }
            return true;
        }
        return false;
    }

    /**
     *  Kiểm tra sự tồn tại của sheet với vị trí chỉ định
     *  @param int $id_sheet Vị trí sheet
     *  @return bool true: tồn tại|false: ko tồn tại
     * **/
    public function sheetExists($id_sheet){
        return $id_sheet>=0 && $id_sheet<$this->sheetCount;
    }

    /**
     * Kiểm tra tên table
     * **/
    public function TableName($id_sheet,$startCell,$endCell,$tableName,$arrTable){
        //có tên table
        if(array_key_exists($tableName,$arrTable)){
            //kiểm tra vùng table có khớp
            if($arrTable[$tableName]['TableRange'] === ($startCell.':'.$endCell)){
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra tên sheet
     * **/
    function sheetName($id_sheet,$sheetName){
        if($this->sheetExists($id_sheet)){
            if($this->spreadsheet->getSheet($id_sheet)->getTitle() === $sheetName){
                return true;
            }
        }
        return false;
    }

    /**
     *  kiểm tra hướng giấy
     *
     * **/
    public function PageOrientation($id_sheet,$type){
        if($this->spreadsheet->getSheet($id_sheet)->getPageSetup()->getOrientation() == strtolower(\App\Enums\PageOrientation::getKey((int)$type))){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Kiểm tra chiều
     * **/
    function RowHeight($id_sheet,$height){

    }

    /**
     * Kiểm tra khổ giấy
     * **/
    function PageSize($id_sheet,$typeSize){
        if($this->sheetExists($id_sheet)){
            $pageSize = $this->spreadsheet->getSheet($id_sheet)->getPageSetup()->getPaperSize();
            switch ($typeSize) {
                case \App\Enums\PageSize::Letter:
                    if($pageSize === 1 || $pageSize === 2){
                        return true;
                    }
                case \App\Enums\PageSize::Tabloid:
                    if($pageSize === 3){
                        return true;
                    }
                case \App\Enums\PageSize::Legal:
                    if($pageSize === 5){
                        return true;
                    }
                case \App\Enums\PageSize::Statement:
                    if($pageSize === 6){
                        return true;
                    }
                case \App\Enums\PageSize::Executive:
                    if($pageSize === 7){
                        return true;
                    }
                case \App\Enums\PageSize::A3:
                    if($pageSize === 8){
                        return true;
                    }
                case \App\Enums\PageSize::A4:
                    if($pageSize === 9 || $pageSize === 10){
                        return true;
                    }
                    break;
                case \App\Enums\PageSize::B4:
                    if($pageSize === 12){
                        return true;
                    }
                case \App\Enums\PageSize::B5:
                    if($pageSize === 13){
                        return true;
                    }
            }
        }
        return false;
    }

    function ColumnWidth($id_sheet,$column,$width){
        if($this->sheetExists($id_sheet)){
            return true;
            $column = Coordinate::coordinateFromString($column)[0];
            dd(round($this->spreadsheet->getSheet($id_sheet)->getColumnDimensions()[$column]->getWidth(),2),floor($this->spreadsheet->getSheet($id_sheet)->getColumnDimensions()[$column]->getWidth('cm')));
            // $spreadsheet->getSheetByName('KQ')->getColumnDimensions()["C"]->getWidth()
        }
        return false;
    }

    /**
     * kiểm tra merge cell center
     * **/
    public function HorizontalCenter($sheetIndex,$startCell,$endCell){
        if($this->spreadsheet->getSheet($sheetIndex)->getStyle($startCell.':'.$endCell)->getAlignment()->getHorizontal() == "center"){
            return true;
        }
        else return false;
    }
}
