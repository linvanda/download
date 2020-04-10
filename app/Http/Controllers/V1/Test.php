<?php

namespace App\Http\Controllers\V1;

use WecarSwoole\Http\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use WecarSwoole\Util\File;

class Test extends Controller
{
    public function index()
    {
        ini_set("memory_limit", "1024M"); 

        $spreadsheet = new Spreadsheet();


        $writer = new Xlsx($spreadsheet);
        $writer->save(File::join(STORAGE_ROOT, 'temp/hello world.xlsx'));
    }
}
