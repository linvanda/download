<?php

use WecarSwoole\Util\File;

include './base.php';

$fileName = "ceshi.ext";
if ($dotPos = strrpos($fileName, '.')) {
    $ext = substr($fileName, $dotPos + 1);
}
echo $ext;