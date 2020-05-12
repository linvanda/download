<?php

namespace App\Http\Controllers\V1;

use App\Domain\Processor\Ticket;
use WecarSwoole\Http\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use WecarSwoole\Util\File;

class Test extends Controller
{
    public function index()
    {
        // ini_set("memory_limit", "1024M"); 

        // $spreadsheet = new Spreadsheet();

        // 使用列缓存
        // $cache = new MyCustomPsr16Implementation();
        // \PhpOffice\PhpSpreadsheet\Settings::setCache($cache);

        // 设置语言
        // $locale = 'pt_br';
        // $validLocale = \PhpOffice\PhpSpreadsheet\Settings::setLocale($locale);
        // if (!$validLocale) {
        //     echo 'Unable to set locale to ' . $locale . " - reverting to en_us" . PHP_EOL;
        // }

        // 获取指定的 worksheet
        // $spreadsheet->getSheet(1);
        // $spreadsheet->getSheetByName('Worksheet 1');
        // $spreadsheet->getActiveSheet();
        // $spreadsheet->getSheetCount();
        // $spreadsheet->getSheetNames();
        // $spreadsheet->setActiveSheetIndex(1);
        // $spreadsheet->setActiveSheetIndexByName('name');

        // 创建 worksheet
        // $spreadsheet->createSheet();
        // // Create a new worksheet called "My Data"
        // $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'My Data');
        // // Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
        // $spreadsheet->addSheet($myWorkSheet, 0);

        // $clonedWorksheet = clone $spreadsheet->getSheetByName('Worksheet 1');
        // $clonedWorksheet->setTitle('Copy of Worksheet 1');
        // $spreadsheet->addSheet($clonedWorksheet);

        // 删除 worksheet
        // $sheetIndex = $spreadsheet->getIndex(
        //     $spreadsheet->getSheetByName('Worksheet 1')
        // );
        // $spreadsheet->removeSheetByIndex($sheetIndex);

        // 设置元数据
        // $spreadsheet->getProperties()
        // ->setCreator("Maarten Balliauw")
        // ->setLastModifiedBy("Maarten Balliauw")
        // ->setTitle("Office 2007 XLSX Test Document")
        // ->setSubject("Office 2007 XLSX Test Document")
        // ->setDescription(
        //     "Test document for Office 2007 XLSX, generated using PHP classes."
        // )
        // ->setKeywords("office 2007 openxml php")
        // ->setCategory("Test result file");

        // 设置文本换行
        // $spreadsheet->getActiveSheet()->getCell('A1')->setValue("hello\nworld");
        // $spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setWrapText(true);

        // 精确设置单元格格式
        // $spreadsheet->getActiveSheet()->getCell('A1')
        // ->setValueExplicit(
        //     '25',
        //     \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
        // );

        // 设置超链接
        // $spreadsheet->getActiveSheet()->setCellValue('E26', 'www.phpexcel.net');
        // $spreadsheet->getActiveSheet()->getCell('E26')->getHyperlink()->setUrl('https://www.example.com');

        // 链接到另一个 worksheet
        // $spreadsheet->getActiveSheet()->setCellValue('E26', 'www.phpexcel.net');
        // $spreadsheet->getActiveSheet()->getCell('E26')->getHyperlink()->setUrl("sheet://'Sheetname'!A1");

        // 设置打印格式（方向、纸张大小）
        //     $spreadsheet->getActiveSheet()->getPageSetup()
        //     ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        // $spreadsheet->getActiveSheet()->getPageSetup()
        //     ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // 设置打印格式：宽高适配
        // $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        // $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        // $spreadsheet->getActiveSheet()->getPageSetup()->setScale(100);

        // 设置打印格式：页边距
        // $spreadsheet->getActiveSheet()->getPageMargins()->setTop(1);
        // $spreadsheet->getActiveSheet()->getPageMargins()->setRight(0.75);
        // $spreadsheet->getActiveSheet()->getPageMargins()->setLeft(0.75);
        // $spreadsheet->getActiveSheet()->getPageMargins()->setBottom(1);

        // 设置打印格式：居中
        // $spreadsheet->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
        // $spreadsheet->getActiveSheet()->getPageSetup()->setVerticalCentered(false);

        // 打印格式：页眉页脚
        //     $spreadsheet->getActiveSheet()->getHeaderFooter()
        //     ->setOddHeader('&C&HPlease treat this document as confidential!');
        // $spreadsheet->getActiveSheet()->getHeaderFooter()
        //     ->setOddFooter('&L&B' . $spreadsheet->getProperties()->getTitle() . '&RPage &P of &N');

        // 打印格式：设置打印区域
        // $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:E5');

        // 设置单元格格式
        // $spreadsheet->getActiveSheet()->getStyle('B2')
            // ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        // $spreadsheet->getActiveSheet()->getStyle('B2')
        //     ->getFill()->getStartColor()->setARGB('FFFF0000');
        // 设置多个单元格格式（推荐）
        // $spreadsheet->getActiveSheet()->getStyle('B3:B7')->getFill()
        // ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        // ->getStartColor()->setARGB('FFFF0000');
        // 通过数组设置（设置数量很多的时候有性能优势）
        // $styleArray = [
        //     'font' => [
        //         'bold' => true,
        //     ],
        //     'alignment' => [
        //         'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
        //     ],
        //     'borders' => [
        //         'top' => [
        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //         ],
        //     ],
        //     'fill' => [
        //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
        //         'rotation' => 90,
        //         'startColor' => [
        //             'argb' => 'FFA0A0A0',
        //         ],
        //         'endColor' => [
        //             'argb' => 'FFFFFFFF',
        //         ],
        //     ],
        // ];
        // $spreadsheet->getActiveSheet()->getStyle('A3')->applyFromArray($styleArray);
        // 设置默认样式
        // $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        // $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
        // $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(12);
        // $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);

        // 条件样式
        // $conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        // $conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        // $conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        // $conditional1->addCondition('0');
        // $conditional1->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        // $conditional1->getStyle()->getFont()->setBold(true);
        // $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        // $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        // $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        // $conditional2->addCondition('0');
        // $conditional2->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN);
        // $conditional2->getStyle()->getFont()->setBold(true);
        // $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('B2')->getConditionalStyles();
        // $conditionalStyles[] = $conditional1;
        // $conditionalStyles[] = $conditional2;
        // $spreadsheet->getActiveSheet()->getStyle('B2')->setConditionalStyles($conditionalStyles);

        // 重用样式
        // $spreadsheet->getActiveSheet()
        // ->duplicateStyle(
        //     $spreadsheet->getActiveSheet()->getStyle('B2'),
        //     'B3:B7'
        // );

        // 设置列过滤
        // $spreadsheet->getActiveSheet()->setAutoFilter('A1:C9');

        // 设置列宽度
        // $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        // $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);// 自动宽度

        // 设置行高
        // $spreadsheet->getActiveSheet()->getRowDimension('10')->setRowHeight(100);//默认是 12.75 pts

        // 合并单元格
        // $spreadsheet->getActiveSheet()->mergeCells('A18:E22');

        // 添加图片
        //  Use GD to create an in-memory image
        // $gdImage = @imagecreatetruecolor(120, 20) or die('Cannot Initialize new GD image stream');
        // $textColor = imagecolorallocate($gdImage, 255, 255, 255);
        // imagestring($gdImage, 1, 5, 5,  'Created with PhpSpreadsheet', $textColor);
        // //  Add the In-Memory image to a worksheet
        // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
        // $drawing->setName('In-Memory image 1');
        // $drawing->setDescription('In-Memory image 1');
        // $drawing->setCoordinates('G10');
        // $drawing->setImageResource($gdImage);
        // $drawing->setRenderingFunction(
        //     \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG
        // );
        // $drawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);
        // $drawing->setHeight(36);
        // $drawing->setWorksheet($spreadsheet->getActiveSheet());


        // // 数据格式化
        // $spreadsheet->getActiveSheet()->getStyle('A1')->getNumberFormat()
        // ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // // Set value binder
        // \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );

        // // Create new Spreadsheet object
        // $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // for ($i = 1; $i < 400; $i++) {
        //     $spreadsheet->getActiveSheet()->setCellValue("A{$i}", 'hello word');
        //     $spreadsheet->getActiveSheet()->setCellValue("B{$i}", 'hello word');
        //     $spreadsheet->getActiveSheet()->setCellValue("C{$i}", 'hello word');
        //     $spreadsheet->getActiveSheet()->setCellValue("D{$i}", 'hello word');
        //     $spreadsheet->getActiveSheet()->setCellValue("E{$i}", 'hello word');
        //     $spreadsheet->getActiveSheet()->setCellValue("F{$i}", 'hello word');
        // }



        // $writer = new Xlsx($spreadsheet);
        // $writer->save(File::join(STORAGE_ROOT, 'temp/hello world.xlsx'));

        // $spreadsheet->disconnectWorksheets();
        // unset($spreadsheet);
    }

    public function sourceData()
    {
        sleep(4);
        $data = [];

        for ($i = 0; $i < 10; $i++) {
            $data[] = ['name' => '三', 'age' => mt_rand(10, 100), 'city' => ['深圳', '上海'][mt_rand(0,1)]];
        }

        $this->return([
            'data' => $data,
            'total' => 10000,
            'header' => ["油站" => '钓鱼岛', '日期' => date('Y-m-d H:i:s')],
            'footer' => ['负责人' => '松林', '签名' => ''],
            'template' => [
                [
                    'name' => 'name',
                    'title' => '姓名',
                    'type' => 'string',
                    'color' => 'red',
                ],
                [
                    'name' => 'age',
                    'title' => '年龄',
                    'type' => 'number',
                ],
                [
                    'name' => 'city',
                    'title' => '城市'
                ]
            ]
        ]);
    }

    /**
     * 测试创建大文件
     */
    public function createBigFile()
    {
        set_time_limit(0);
        $f = fopen(File::join(STORAGE_ROOT, "temp/big_file.csv"), 'w');
        
        for ($i = 0; $i < 6000000; $i++) {
            fputcsv($f, ["张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林","张松林",]);
        }
        fclose($f);
    }

    /**
     * 测试下载大文件
     */
    public function download()
    {
        set_time_limit(0);
        $fileName = File::join(STORAGE_ROOT, "temp/big_file.csv");
        $title = "测试大文件.csv";
        $this->response()->withHeader("Content-Disposition", "attachment; filename=$title");
        $this->response()->sendFile($fileName);
    }
}
