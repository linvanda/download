<?php

namespace App\Http\Routes;

use WecarSwoole\Http\Route;

class Routes extends Route
{
    public function map()
    {
        $this->get("/v1/test", "/V1/Test/index");
        $this->get("/v1/test/download", "/V1/Test/download");
        $this->get("/v1/test/create", "/V1/Test/createBigFile");
    }
}
