<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Excel;
use App\Domain\Object\Template\Excel\Tpl;
use App\Domain\Object\Template\Excel\ColHead;
use App\Domain\Object\Template\Excel\Node;
use App\Domain\Object\Template\Excel\RowHead;
use App\Domain\Object\Template\Excel\Style;
use App\Domain\Source\Source;
use App\Exceptions\FileException;
use App\Exceptions\ObjectException;
use EasySwoole\EasySwoole\Config;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use SplQueue;
use App\ErrCode;
use WecarSwoole\Util\File;

/**
 * Excel 文件生成器
 * 根据源文件的行数和大小决定生成几个目标文件
 * 如果生成多个目标文件（或者单个文件达到一定尺寸），则执行归档压缩
 */
class ExcelGenerator
{
    public function generate(Source $source, Excel $target)
    {
        if (!file_exists($source->fileName())) {
            throw new FileException("源文件不存在：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        // 计算需要生成多少个文件，每个文件最大多少行，并算出每个文件名称
        list($fileCount, $fileRowCount) = $this->calcFileCount($source, $target->getTpl()->rowHead() ? true : false);
        $fileNames = $this->calcFileNames($target->objectFileName(), $fileCount);

        if (!$sourceFile = @fopen($source->fileName(), 'r')) {
            throw new FileException("打开源文件失败：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        try {
            // 先从第一行读取列标题
            $colTitles = fgetcsv($sourceFile);

            foreach ($fileNames as $index => $fileName) {
                // 生成 excel。最后一个文件的 row 不做限制
                $maxRow = $index < count($fileNames) - 1 ? $fileRowCount : PHP_INT_MAX;
                $this->createExcel($sourceFile, $fileName, $maxRow, $colTitles, $target);
            }
        } catch (\Exception $e) {
            throw new ObjectException($e->getMessage(), $e->getCode());
        } finally {
            fclose($sourceFile);
            // 删除源文件
            unlink($source->fileName());
        }

        // 如果涉及到多个文件，则压缩
        if (count($fileNames) > 1) {
            $newTargetFileName = (new Archive(File::join($target->getBaseDir(), 'object'), $fileNames))();
            // 重新设置目标文件名字
            $target->setObjectFileName($newTargetFileName);
        }
    }

    /**
     * 生成 excel 文件
     * @param resource $sourceFile 源数据文件，资源对象
     * @param string $objFileName 目标文件名
     * @param int $maxRow 最大读取行数
     * @param array $colTitles 列名数组
     * @param Excel $target 目标对象
     */
    private function createExcel($sourceFile, string $objFileName, int $maxRow, array $colTitles, Excel $target)
    {
        $spreadSheet = new Spreadsheet();
        $activeSheet = $spreadSheet->getActiveSheet();

        $this->setDefaultStyle($spreadSheet, $target);

        // 生成模板
        list($rowOffset, $colOffset, $rowMap, $colMap) = $this->createSheetTpl($activeSheet, $target);

        $rowHeadIndex = array_search(RowHead::NODE_ROW_HEADER_COL, $colTitles);// 行标题索引位置（针对有行表头的）
        $rowHeadUsed = [];// 行标题内部偏移值

        // 循环读取源数据写入到 excel 中
        while ($maxRow-- && !feof($sourceFile)) {
            if (!$rowValues = fgetcsv($sourceFile)) {
                continue;
            }

            $rowOffset++;

            /**
             * 填充一行数据
             */
            // 确定行号
            $theRowNum = $rowOffset;
            // 如果有 rowMap，则使用 rowMap 的行号
            if ($rowMap && $rowHeadIndex !== false) {
                $theRowName = $rowValues[$rowHeadIndex];
                if (isset($rowMap[$theRowName])) {
                    $innerIndex = isset($rowHeadUsed[$theRowName]) ? $rowHeadUsed[$theRowName] + 1 : 0;
                    if (!$theRowNum = $rowMap[$theRowName][$innerIndex] ?? 0) {
                        continue;
                    }

                    $rowHeadUsed[$theRowName] = isset($rowHeadUsed[$theRowName]) ? $rowHeadUsed[$theRowName] + 1 : 0;
                }
            }
            // 遍历每列值填充到 excel 中
            foreach ($rowValues as $index => $val) {
                // 确定列号
                if (!$theColNum = ($colMap[$colTitles[$index]] ?? 0)) {
                    continue;
                }

                $activeSheet->getCell(Coordinate::stringFromColumnIndex($theColNum) . $theRowNum)->setValue($val);
            }

            // 设置行高度（使用默认行高度无效）
            $activeSheet->getRowDimension($theRowNum)->setRowHeight($target->getDefaultHeight());
        }

        // 将 colOffset 偏移到结束位置
        $colOffset += in_array(RowHead::NODE_ROW_HEADER_COL, $colTitles) ? count($colTitles) - 1 : count($colTitles);

        // 设置整个表格边框
        $activeSheet->getStyle('A1' . ':' . Coordinate::stringFromColumnIndex($colOffset) . $rowOffset)
        ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 设置页脚
        if ($target->getFooter()) {
            $this->setFooter($activeSheet, $target->getFooter(), $colOffset, $rowOffset);
        }

        $writer = new Xlsx($spreadSheet);
        $writer->save($objFileName);

        $spreadSheet->disconnectWorksheets();
        unset($spreadSheet);
    }

    /**
     * 生成 excel 模板
     * @return array [当前行号, 当前列号, 行映射, 列映射]
     */
    private function createSheetTpl(Worksheet $activeSheet, Excel $target): array
    {
        // 必须有 template
        if (!$tpl = $target->getTpl()) {
            throw new ObjectException("缺少 template");
        }

        $rowHead = $tpl->rowHead();
        $colNum = $this->calcColNum($tpl);
        $currRowNum = 0;
        
        // 标题
        if ($target->getTitle()) {
            $this->setTitle($activeSheet, $target->getTitle(), $colNum);
            $currRowNum++;
        }

        // 摘要
        if ($target->getSummary()) {
            $this->setSummary($activeSheet, $target->getSummary(), $colNum, $currRowNum);
            $currRowNum++;
        }

        // header
        if ($target->getHeader()) {
            $currRowNum += $this->setHeader($activeSheet, $target->getHeader(), $colNum, $currRowNum);
        }

        // 列标头
        $colMap = $this->setColHead($activeSheet, $tpl->colHead(), $rowHead ? $rowHead->deep() - 1 : 0, $currRowNum);
        $currRowNum += $tpl->colHead()->deep() - 1;

        // 行标头
        $rowMap = [];
        if ($rowHead) {
            $rowMap = $this->setRowHead($activeSheet, $rowHead, $currRowNum);
        }

        return [$currRowNum, $rowHead ? $rowHead->deep() - 1 : 0, $rowMap, $colMap];
    }

    /**
     * 设置行标题
     * 每个节点对应一个格子
     * 需要确定每个节点在表格中的位置以及需要合并的行列数
     * 节点所在的层代表其所在的列
     * 非叶子节点只需要合并行，无需合并列；叶子节点只需要合并列，无需合并行
     * 非叶子节点拥有的叶子节点数目就是它要合并的行数
     * 叶子节点需要合并的列数等于树最大层 - 其所在的层
     * 行标题左侧节点在表格上面
     * 采用广度优先遍历
     * @return array 行映射表。格式：[行名 => [行号列表]]
     */
    private function setRowHead(Worksheet $worksheet, RowHead $rowHead, int $lastRowNum): array
    {
        return $this->setExcelSubHead($worksheet, $rowHead, 0, $lastRowNum, 2);
    }

    /**
     * 设置列标题
     * 每个节点对应一个格子
     * 需要确定每个节点在表格中的位置以及需要合并的行列数
     * 节点所在的层数代表它所在的行
     * 非叶节点只需要考虑合并列，叶节点需要同时考虑合并列和行，其中需要合并的行数=树最大层 - 其所在层
     * 非叶节点拥有的叶节点数目就是它要合并的列单元格数
     * 采用广度优先遍历
     * @param Worksheet $worksheet
     * @param ColHead $colHead 列表头树
     * @param int $rowHeadColNum 行表头占用的列数
     * @param int $lastRowNum 最新行号，需要从下一行开始
     * @return array 列映射表，格式：[列名 => 列号]
     */
    private function setColHead(Worksheet $worksheet, ColHead $colHead, int $rowHeadColNum, int $lastRowNum): array
    {
        // 如果有行标题，则需要预留相应的列给行标题
        if ($rowHeadColNum) {
            $worksheet->mergeCells("A" . ($lastRowNum + 1) . ":"
            . Coordinate::stringFromColumnIndex($rowHeadColNum) . ($lastRowNum + $colHead->deep() - 1));
        }

        $colMap = $this->setExcelSubHead($worksheet, $colHead, $rowHeadColNum, $lastRowNum, 1);

        return array_map(function ($item) {
            return $item[0];
        }, $colMap);
    }

    /**
     * 参见 setColHead(...) 的说明
     * @param Worksheet $worksheet
     * @param Node $headTree 行/列表头节点树
     * @param int $rowOffset 行偏移量
     * @param int $colOffset 列偏移量
     * @param int $type 1：生成列标题，2 生成行标题
     * @return array 行列映射表。格式：[行/列名称 => [行/列号]]
     */
    private function setExcelSubHead(Worksheet $worksheet, Node $headTree, int $colOffset, int $rowOffset, int $type = 1): array
    {
        $depth = $headTree->deep() - 1;// 根节点不计入列表头深度
        $map = [];// 行列映射表。格式：[行/列名称 => [行/列号]]
        $styleMap = [];// 行列样式，格式：行/列号 => 样式对象

        // 使用队列实现广度优先遍历
        $queue = new SplQueue();
        $queue->enqueue($headTree);

        while (1) {
            // 退出遍历条件：队列为空
            if ($queue->isEmpty()) {
                break;
            }

            /**
             * 取出当前需要处理的节点
             * @var ColHead
             */
            $node = $queue->dequeue();

            // 顶层节点不对应任何单元格
            if ($node->name() == Node::NODE_TOP) {
                goto next;
            }

            $pos = $node->getPosition();

            /**
             * 将节点转化为单元格
             * 注意 merge 值等于 1 表示不需要合并（仅和它自身合并）
             */
            if ($type == 1) {
                // 该节点需要合并的列数等于该节点子树的广度
                $mergeColNum = $node->breadth();
                // 该节点需要合并的行数等于该节点的深度差(由于$depth已经减去1了，所以这里需要加1补回去)
                $mergeRowNum = $node->isLeaf() ? $depth - $pos[0] + 1 : 1;
            } else {
                // 行标题和列标题反过来
                $mergeColNum = $node->isLeaf() ? $depth - $pos[0] + 1 : 1;
                $mergeRowNum = $node->breadth();
            }
            
            // 设置单元格
            // 注意：树节点的位置从 0 开始的，要加 1（由于深度方向上已经去掉顶层节点了，所以此方向不需要再加 1）
            $fromRow = $rowOffset + ($type == 1 ? $pos[0] : $pos[1] + 1);
            $fromCol = $colOffset + ($type == 1 ? $pos[1] + 1 : $pos[0]);
            if ($mergeColNum > 1 || $mergeRowNum > 1) {
                $toRow = $fromRow + $mergeRowNum - 1;
                $toCol = $fromCol + $mergeColNum - 1;
                $worksheet->mergeCells(Coordinate::stringFromColumnIndex($fromCol)
                . $fromRow . ':' . Coordinate::stringFromColumnIndex($toCol) . $toRow);
            }

            $worksheet->getCell(Coordinate::stringFromColumnIndex($fromCol) . $fromRow)->setValue($node->title());

            // 叶子节点的特殊处理
            if ($node->isLeaf()) {
                // 保存行列映射
                if (!isset($map[$node->name()])) {
                    $map[$node->name()] = [];
                }

                if ($type == 1) {
                    $map[$node->name()][] = $fromCol;
                } elseif ($node instanceof RowHead) {
                    // 行映射，一个节点可能对应多行
                    for ($i = 0; $i < $node->rowCount(); $i++) {
                        $map[$node->name()][] = $fromRow + $i;
                    }
                }

                // 行列样式
                $styleMap[$type == 1 ? $fromCol : $fromRow] = $node->style();
            }

            next:
            // 将该节点的孩子节点依次入列
            foreach ($node->children() as $child) {
                $queue->enqueue($child);
            }
        }

        // 设置表头样式
        $this->setCRHeadStyle(
            $worksheet,
            1,
            $rowOffset + 1,
            $type == 1 ? $colOffset + $headTree->breadth() : $depth,
            $rowOffset + ($type == 1 ? $depth : $headTree->breadth())
        );

        // 设置行列样式
        if ($type == 1) {
            $this->setColStyle($worksheet, $styleMap);
        } else {
            $this->setRowStyle($worksheet, $styleMap);
        }

        return $map;
    }

    /**
     * 设置列样式
     * 目前仅支持设置行宽度
     */
    private function setColStyle(Worksheet $worksheet, array $colStyleMap)
    {
        foreach ($colStyleMap as $colNo => $style) {
            if (!$style || !$style instanceof Style) {
                continue;
            }

            if ($style->getWidth()) {
                $worksheet->getColumnDimension(Coordinate::stringFromColumnIndex($colNo))->setWidth($style->getWidth());
            }
        }
    }

    /**
     * 设置行样式
     */
    private function setRowStyle(Worksheet $worksheet, array $rowStyleMap)
    {
        // 暂时不设置任何行样式
    }

    private function setCRHeadStyle(Worksheet $worksheet, int $startCol, int $startRow, int $endCol, int $endRow)
    {
        $style = $worksheet->getStyle(Coordinate::stringFromColumnIndex($startCol) . $startRow
        . ':' . Coordinate::stringFromColumnIndex($endCol) . $endRow);
        $style->getFont()->setBold(true)->setSize(16);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DFDFDF');
    }

    /**
     * 设置 Excel header
     * 第一版对 header 简化处理：全部排布在一行
     * @return int header 占用了几行
     */
    private function setHeader(Worksheet $worksheet, array $headers, int $colCount, int $lastRowNum): int
    {
        $this->setSimpleHeaderFooter($worksheet, $headers, $colCount, $lastRowNum, 1);
        return 1;
    }

    /**
     * 设置 Excel Summary
     */
    private function setSummary(Worksheet $worksheet, string $summary, int $colCount, int $lastRowNum)
    {
        if (!$summary) {
            return;
        }

        // 从下一行开始
        $currRowNum = $lastRowNum + 1;

        $coordinate = "A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colCount) . $currRowNum;
        $worksheet->mergeCells($coordinate);
        $worksheet->getRowDimension($currRowNum)->setRowHeight(36);
        $cell = $worksheet->getCell("A{$currRowNum}");
        // 自动换行
        $style = $cell->getStyle();
        $style->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
        $cell->setValue($summary);
    }

    /**
     * 设置 Excel title
     */
    private function setTitle(Worksheet $worksheet, string $title, int $colCount, int $lastRowNum = 0)
    {
        if (!$title) {
            return;
        }

        // 从下一行开始
        $currRowNum = $lastRowNum + 1;

        $coordinate = "A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colCount) . $currRowNum;
        $worksheet->mergeCells($coordinate);
        $worksheet->getRowDimension($currRowNum)->setRowHeight(36);
        $cell = $worksheet->getCell("A{$currRowNum}");
        $cell->setValue($title);
        $style = $cell->getStyle();
        $style->getFont()->setSize(22)->setBold(true);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * 设置 Excel 页脚
     */
    private function setFooter(Worksheet $worksheet, array $footers, int $colCount, int $lastRowNum)
    {
        $this->setSimpleHeaderFooter($worksheet, $footers, $colCount, $lastRowNum);
    }

    private function setSimpleHeaderFooter(Worksheet $worksheet, array $contents, int $colCount, int $lastRowNum, int $hasBorder = 0)
    {
        $txt = "";
        foreach ($contents as $key => $val) {
            $txt .= $key . '：' . $val . "        ";
        }

        // 从下一行开始
        $currRowNum = $lastRowNum + 1;

        $coordinate = "A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colCount) . $currRowNum;
        $worksheet->mergeCells($coordinate);
        $cell = $worksheet->getCell("A{$currRowNum}");
        $cell->setValue($txt);
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension($currRowNum)->setRowHeight(30);
    }

    /**
     * 设置 Excel 默认样式
     */
    private function setDefaultStyle(Spreadsheet $workSheet, Excel $target)
    {
        $workSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth($target->getDefaultWidth());
        $workSheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight($target->getDefaultHeight());
    }

    /**
     * 计算 excel 列总数
     */
    private function calcColNum(Tpl $tpl): int
    {
        $cBreadth = $tpl->colHead()->breadth();
        $rDeep = $tpl->rowHead() ? $tpl->rowHead()->deep() - 1 : 0;
        return $cBreadth + $rDeep;
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
    private function calcFileCount(Source $source, bool $hasRowHead = false): array
    {
        // 有行标题的情况下只生成一个文件
        if ($hasRowHead) {
            return [1, PHP_INT_MAX];
        }

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
