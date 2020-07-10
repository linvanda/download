<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Template\Excel\Tpl;
use App\Domain\Object\Obj;
use App\Domain\Object\Template\Excel\ColHead;
use App\Domain\Object\Template\Excel\Node;
use App\Domain\Object\Template\Excel\RowHead;
use App\Domain\Source\Source;
use App\Exceptions\FileException;
use App\Exceptions\ObjectException;
use EasySwoole\EasySwoole\Config;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SplQueue;
use App\ErrCode;

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
        $fileNames = $this->calcFileNames($object->objectFileName(), $fileNum);

        if (!$sourceFile = @fopen($source->fileName(), 'r')) {
            throw new FileException("打开源文件失败：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        try {
            // 先从第一行读取列标题
            $colTitles = fgetcsv($sourceFile);
            // 生成 excel 模板
            list($sheetTpl, $rowCursor, $colCursor) = $this->createSheetTpl($object->getMeta());
            foreach ($fileNames as $index => $fileName) {
                // 生成 excel。最后一个文件的 row 不做限制
                $row = $index < count($fileNames) - 1 ? $fileRow : PHP_INT_MAX;
                $this->createExcel($sourceFile, $fileName, $row, $colTitles, $sheetTpl, $rowCursor + 1, $colCursor + 1);
            }

            // 如果涉及到多个文件，则压缩

        } catch (\Exception $e) {
            throw new ObjectException($e->getMessage(), $e->getCode());
        } finally {
            fclose($sourceFile);
            // 删除源文件
            unlink($source->fileName());
        }
    }

    /**
     * 生成 excel 模板
     * $meta 组成：title、summary、header、footer、template
     * 单元格（列）总数 = 行标头深度 + 列标头宽度 - 1
     * @return array [Worksheet, 当前行号, 当前列号]
     */
    private function createSheetTpl(array $meta): array
    {
        // 必须有 template
        $tpl = $meta['template'] ?? null;
        if (!$tpl || !$tpl instanceof Tpl) {
            throw new ObjectException("缺少 template");
        }

        $colNum = $this->calcColNum($tpl);
        $currRowNum = 0;
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

        $rowHead = $tpl->rowHead();
        // 列标头
        $currRowNum += $this->setColHead($activeSheet, $tpl->colHead(), $rowHead ? $rowHead->deep() : 0, $currRowNum);

        // 行标头
        if ($rowHead) {
            $this->setRowHead($activeSheet, $rowHead, $currRowNum);
        }

        return [$activeSheet, $currRowNum, $rowHead ? $rowHead->deep() : 0];
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
     */
    private function setRowHead(Worksheet $worksheet, RowHead $rowHead, int $lastRowNum)
    {
        $maxDepth = $rowHead->deep();

        // 列游标（各列当前行所在位置）
        $colsCursor = [];
        for ($i = 1; $i <= $maxDepth; $i++) {
            $colsCursor[$i] = $lastRowNum;
        }

        // 使用队列实现广度优先遍历
        $queue = new SplQueue();
        $queue->enqueue($rowHead);

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
            $pos = $node->getPosition();

            /**
             * 将节点转化为单元格
             * 注意 merge 值等于 1 表示不需要合并（仅和它自身合并）
             */
            // 该节点需要合并的行数等于该节点子树的广度
            $mergeRowNum = $node->breadth();
            // 该节点需要合并的列数
            $mergeColNum = $node->isLeaf() ? $maxDepth - $pos[0] : 1;

            // 设置单元格
            $fromCol = $pos[0] + 1;
            $fromRow = $lastRowNum + $colsCursor[$fromCol] + 1;
            if ($mergeColNum > 1 || $mergeRowNum > 1) {
                $toCol = $pos[0] + $mergeColNum;
                $toRow = $lastRowNum + $colsCursor[$fromCol] + $mergeRowNum;
                $worksheet->mergeCells(Coordinate::stringFromColumnIndex($fromCol) . $fromRow . ':' . Coordinate::stringFromColumnIndex($toCol) . $toRow);
            }
            $worksheet->getCell(Coordinate::stringFromColumnIndex($fromCol) . $fromRow)->setValue($node->title());

            //更新列游标
            for ($i = $pos[0] + 1; $i <= $pos[0] + $mergeColNum; $i++) {
                $colsCursor[$i] += $mergeRowNum;
            }

            // 将该节点的孩子节点依次入列
            foreach ($node->children() as $child) {
                $queue->enqueue($child);
            }
        }
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
     * @return int 列表头占用的行数
     */
    private function setColHead(Worksheet $worksheet, ColHead $colHead, int $rowHeadColNum, int $lastRowNum): int
    {
        $colDepth = $colHead->deep() - 1;// 根节点不计入列表头深度

        // 如果有行标题，则需要预留相应的列给行标题
        if ($rowHeadColNum) {
            $worksheet->mergeCells("A" . ($lastRowNum + 1) . ":" . Coordinate::stringFromColumnIndex($rowHeadColNum) . ($lastRowNum + $colDepth));
        }

        // 使用队列实现广度优先遍历
        $queue = new SplQueue();
        $queue->enqueue($colHead);

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

            // 忽略特殊占位节点
            if ($node->name() == Node::NODE_ROW_HEADER_COL) {
                continue;
            }

            // 顶层节点不对应任何单元格
            if ($node->name() == Node::NODE_TOP) {
                goto next;
            }

            $pos = $node->getPosition();

            /**
             * 将节点转化为单元格
             * 注意 merge 值等于 1 表示不需要合并（仅和它自身合并）
             */
            // 该节点需要合并的列数等于该节点子树的广度
            $mergeColNum = $node->breadth();
            // 该节点需要合并的行数(由于$colDepth已经减去1了，所以这里需要加1补回去)
            $mergeRowNum = $node->isLeaf() ? $colDepth - $pos[0] + 1 : 1;

            // 设置单元格
            $fromRow = $lastRowNum + $pos[0];
            $fromCol = $pos[1] + 1;// 树节点的位置从 0 开始的
            if ($mergeColNum > 1 || $mergeRowNum > 1) {
                $toRow = $lastRowNum + $pos[0] + $mergeRowNum - 1;
                $toCol = $fromCol + $mergeColNum - 1;
                $worksheet->mergeCells(Coordinate::stringFromColumnIndex($fromCol) . $fromRow . ':' . Coordinate::stringFromColumnIndex($toCol) . $toRow);
            }
            $worksheet->getCell(Coordinate::stringFromColumnIndex($fromCol) . $fromRow)->setValue($node->title());

            next:
            // 将该节点的孩子节点依次入列
            foreach ($node->children() as $child) {
                $queue->enqueue($child);
            }
        }

        return $colDepth;
    }

    /**
     * 设置 Excel header
     * 第一版对 header 简化处理：全部排布在一行
     * @return int header 占用了几行
     */
    private function setHeader(Worksheet $worksheet, array $headers, int $colNum, int $currRowNum): int
    {
        $headerTxt = "";
        foreach ($headers as $key => $val) {
            $headerTxt .= $key . '：' . $val . "    ";
        }

        // 从下一行开始
        $currRowNum += 1;

        $worksheet->mergeCells("A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colNum > self::MAX_MGR_COL_NUM ? self::MAX_MGR_COL_NUM : $colNum) . $currRowNum);
        $worksheet->getCell("A{$currRowNum}")->setValue($headerTxt);

        return 1;
    }

    /**
     * 设置 Excel Summary
     */
    private function setSummary(Worksheet $worksheet, string $summary, int $colNum, int $currRowNum)
    {
        if (!$summary) {
            return;
        }

        // 从下一行开始
        $currRowNum += 1;

        $worksheet->mergeCells("A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colNum > self::MAX_MGR_COL_NUM ? self::MAX_MGR_COL_NUM : $colNum) . $currRowNum);
        $summaryCell = $worksheet->getCell("A{$currRowNum}");
        $summaryCell->setValue($summary);
    }

    /**
     * 设置 Excel title
     */
    private function setTitle(Worksheet $worksheet, string $title, int $colNum, int $currRowNum = 0)
    {
        if (!$title) {
            return;
        }

        // 从下一行开始
        $currRowNum += 1;

        $worksheet->mergeCells("A{$currRowNum}:" . Coordinate::stringFromColumnIndex($colNum > self::MAX_MGR_COL_NUM ? self::MAX_MGR_COL_NUM : $colNum) . $currRowNum);
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

        // 存在 rowHead 的情况下，colHead 中包含一列 rowHead 用的专用占位列，因而计算上需要减 1
        return $cBreadth + $rDeep - ($rDeep ? 1 : 0);
    }

    /**
     * 生成 excel 文件
     * @param resource $sourceFile 源数据文件，资源对象
     * @param string $objFileName 目标文件名
     * @param int $maxRow 最大读取行数
     * @param array $colTitles 列名数组
     * @param array $colMap 列名和列号映射数组
     * @param Worksheet $sheetTpl excel 模板
     * @param int $startRow 开始行号
     * @param int $startCol 开始列号
     */
    private function createExcel($sourceFile, string $objFileName, int $maxRow, array $colTitles, Worksheet $sheetTpl, int $startRow, int $startCol)
    {
        $worksheet = clone $sheetTpl;

        // 循环读取源数据写入到 excel 中
        // while ($maxRow-- && !feof($sourceFile)) {
        //     if (!$oneColValues = fgetcsv($sourceFile)) {
        //         continue;
        //     }

        //     foreach ($oneColValues as $val) {
        //         $worksheet->getCell(Coordinate::stringFromColumnIndex($startCol++) . $startRow)->setValue($val);
        //     }
            
        //     $startRow++;
        // }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($spreadsheet->getSheetByName('Worksheet')));
        $spreadsheet->addSheet($worksheet);

        $writer = new Xlsx($spreadsheet);
        $writer->save($objFileName);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
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
