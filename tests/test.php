<?php

include "./base.php";

$f = new \App\Foundation\File\LocalFile("./test.csv");
$data = [
    ['name' => '张三', 'age' => 18],
    ['name' => '张三', 'age' => 18],
    ['name' => '张三', 'age' => 18],
];

echo "size:",$f->size(),"\n";
$f->saveAsCsv($data);
echo "size:",$f->size(),"\n";
$data2 = [
    ['name' => '张三', 'age' => 18],
    ['name' => '张三', 'age' => 18],
    ['name' => '张三', 'age' => 18],
];
$f->saveAsCsv($data);
echo "size:",$f->size(),"\n";
$f->close();
