<?php

namespace App\Domain\Source;

use App\Domain\Target\Target;
use App\Foundation\Client\API;

/**
 * 源数据服务
 */
class SourceService
{
    /**
     * 获取数据
     */
    public function fetch(ISource $source, Target $target)
    {
        // 获取动态元数据
        $this->fetchDynamicMeta($source, $target);
        // 获取数据
        $source->fetch(new API(), $target->type() === Target::TYPE_EXCEL ? true : false);
    }

    /**
     * 获取动态元数据
     * 动态元数据是指在生成源数据时动态生成的元数据，这些元数据一般取决于数据本身，因而需要动态生成
     */
    private function fetchDynamicMeta(ISource $source, Target $target)
    {
        // 目前并无动态模板的需求，而且动态模板需要先拉取一条数据，有些数据源实现方没有很好地处理分页问题导致严重的性能问题
        // 故先禁掉该功能，以后如果确实需要再开启
//        if (!$metaData = $source->fetchMeta(new API())) {
//            return;
//        }
//
//        // 动态元数据不保存到数据库中
//        $target->setMeta($metaData);
    }
}
