<?php

include "./base.php";

$a = ['abc'];
$s = json_encode($a);
try {
    $b = unserialize($s);
    echo "--$b--";
} catch (\Throwable $e) {
    echo "aaa\n";
}
