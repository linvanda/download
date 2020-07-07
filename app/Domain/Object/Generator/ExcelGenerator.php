<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Template\Excel\Tpl;
use App\Domain\Object\Obj;
use App\Domain\Source\Source;
use App\ErrCode;
use App\Exceptions\FileException;
use App\Exceptions\ObjectException;
use EasySwoole\EasySwoole\Config;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel 文件生成器
 * 根据源文件的行数和大小决定生成几个目标文件
 * 如果生成多个目标文件（或者单个文件达到一定尺寸），则执行归档压缩
 */
class ExcelGenerator implements IGenerator
{
    private const MAX_MGR_COL_NUM = 30;

    public function generate(Source $source, Obj $object)
    {
        if (!file_exists($source->fileName())) {
            throw new FileException("源文件不存在：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        // 计算需要生成多少个文件，每个文件最大多少行，并算出每个文件名称
        list($fileNum, $fileRow) = $this->calcFileCount($source);
        $fileNames = $this->calcFileNames($object->downloadFileName(), $fileNum);

        if (!$sourceFile = @fopen($source->fileName(), 'r')) {
            throw new FileException("打开源文件失败：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        try {
            // 先从第一行读取列标题
            $colTitles = fgetcsv($sourceFile);
            // 生成 sheet 模板
            $sheetTpl = $this->createSheetTpl($object->getMeta());

            foreach ($fileNames as $index => $fileName) {
                // 生成 excel。最后一个文件的 row 不做限制
                $row = $index < count($fileNames) - 1 ? $fileRow : PHP_INT_MAX;
                $this->createExcel($sourceFile, $fileName, $fileNum, $index, $row, $colTitles);
            }

            // 如果涉及到多个文件，则压缩

        } catch (\Exception $e) {
            throw new ObjectException($e->getMessage(), $e->getCode());
        } finally {
            fclose($source->fileName());
            // 删除源文件
            unlink($source->fileName());
        }
    }

    /**
     * 生成 excel 模板
     * $meta 组成：title、summary、header、footer、template
     * 单元格（列）总数 = 行标头深度 + 列标头宽度 - 1
     */
    private function createSheetTpl(array $meta)
    {
        // 必须有 template
        $tpl = $meta['template'] ?? null;
        if (!$tpl || !$tpl instanceof Tpl) {
            throw new ObjectException("缺少 template");
        }

        $colNum = $this->calcColNum($tpl);
        $currRowNum = 1;
        $workSheet = new Spreadsheet();
        $activeSheet = $workSheet->getActiveSheet();

        // 设置默认样式
        $this->setDefaultStyle($workSheet);

        // 标题
        if (isset($meta['title'])) {
            $this->setTitle($activeSheet, $meta['title'], $colNum);
            $currRowNum++;
        }

        // 摘要
        if (isset($meta['summary'])) {
            $this->setSummary($activeSheet, $meta['summary'], $colNum, $currRowNum);
            $currRowNum++;
        }

        // header
        if (isset($meta['header'])) {
            $currRowNum += $this->setHeader($activeSheet, $meta['header'], $colNum, $currRowNum);
        }

        // 行标头、列标头
    }

    /**
     * 设置 Excel header
     * 第一版对 header 简化处理：全部排布在一行
     * @return int header 占用了几行
     */
    private function setHeader(Worksheet $worksheet, array $headers, int $colNum, int $currRowNum): int
    {
        $txt = "";
        foreach ($headers as $key => $val) {
            $txt .= $key . '：' . $this->formatHeaderText($val);
        }

        return 1;
    }

    /**
     * 如果 $text 长度不足，则最多进行 $pad 个汉字长度填充（一个汉字相当于 2 个空格）
     */
    private function formatHeaderText(string $text, int $pad = 8): string
    {
        mb_strlen($text, 'utf-8');
    }

    /**
     * 设置 Excel Summary
     */
    private function setSummary(Worksheet $worksheet, string $summary, int $colNum, int $currRowNum)
    {
        if (!$summary) {
            return;
        }

        $worksheet->mergeCells("A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colNum > self::MAX_MGR_COL_NUM ? self::MAX_MGR_COL_NUM : $colNum + 1) . $currRowNum);
        $summaryCell = $worksheet->getCell("A{$currRowNum}");
        $summaryCell->setValue($summary);
    }

    /**
     * 设置 Excel title
     */
    private function setTitle(Worksheet $worksheet, string $title, int $colNum, int $currRowNum = 1)
    {
        if (!$title) {
            return;
        }

        $worksheet->mergeCells("A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colNum > self::MAX_MGR_COL_NUM ? self::MAX_MGR_COL_NUM : $colNum + 1) . $currRowNum);
        $titleCell = $worksheet->getCell("A{$currRowNum}");
        $titleCell->setValue($title);
        $titleCell->getStyle()->getFont()->setSize(18);
    }

    /**
     * 设置 Excel 默认样式
     */
    private function setDefaultStyle(Spreadsheet $workSheet)
    {
        $workSheet->getDefaultStyle()->getFont()->setName('Arial');
        $workSheet->getDefaultStyle()->getFont()->setSize(8);
        $workSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(12);
        $workSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
    }

    /**
     * 计算 excel 列总数
     */
    private function calcColNum(Tpl $tpl): int
    {
        $cBreadth = $tpl->colHead()->breadth();
        $rDeep = $tpl->rowHead() ? $tpl->rowHead()->deep() : 0;

        // 存在 rowHead 的情况下，colHead 中包含列一列 rowHead 用的专用占位列，因而计算上需要减 1
        return $cBreadth + $rDeep - ($rDeep ? 1 : 0);
    }

    private function createExcel($sourceFile, string $objFileName, int $totalFileNum, int $index, int $row, array $colTitles)
    {
        $i = 0;
        while ($i++ < $row && !feof($sourceFile)) {
            if (!$colVal = fgetcsv($sourceFile)) {
                continue;
            }
        }
    }

    private function calcFileNames(string $origFileName, int $fileNum): array
    {
        if ($fileNum === 1) {
            return [$origFileName];
        }

        $fileNames = [];
        $fnameArr = explode('.', $origFileName);
        $ext = array_pop($fnameArr);
        $base = implode('.', $fnameArr);

        for ($i = 0; $i < $fileNum; $i++) {
            $fileNames[] = implode('', [$base, "_{$i}", ".{$ext}"]);
        }

        return $fileNames;
    }

    /**
     * 计算目标文件数目以及每个文件最大行数
     * @return array [文件数目, 最大行数]
     */
    private function calcFileCount(Source $source): array
    {
        $maxSize = Config::getInstance()->getConf("excel_max_size");
        $maxCount = Config::getInstance()->getConf("excel_max_count");
        $sourceSize = $source->size();
        $sourceCount = $source->count();

        if ($sourceSize <= $maxSize * 1.5 && $sourceCount <= $maxCount * 1.5) {
            return [1, PHP_INT_MAX];
        }

        $count = max(ceil($sourceSize / $maxSize), ceil($sourceCount / $maxCount));

        return [$count, ceil($sourceCount / $count)];
    }
}
