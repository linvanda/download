<?php

namespace App\Http\Routes;

use WecarSwoole\Http\Route;

class Routes extends Route
{
    public function map()
    {
        // -------- 测试用 --------
        $this->get("/v1/test", "/V1/Test/index");
        $this->get("/v1/test/download", "/V1/Test/download");
        $this->get("/v1/test/create", "/V1/Test/createBigFile");
        $this->get("/v1/test/source", "/V1/Test/sourceData");
        $this->get("/v1/test/upload", "/V1/Test/upload");
        // -------- 测试用 End --------

        /**
         * 下载数据
         */
        $this->get('/v1/download/{ticket}', '/V1/Download/getData');
    }
}
