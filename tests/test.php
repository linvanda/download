<?php

include "./base.php";

// use App\Domain\Object\Template\Excel\Node;

// $pnode = new Node("top", "top");
// $node1 = new Node("1", "");
// $node2 = new Node("2", "");
// $node3 = new Node("3", "");
// $node4 = new Node("4", "");
// $node5 = new Node("5", "");
// $node6 = new Node("6", "");
// $node7 = new Node("7", "");
// $node8 = new Node("8", "");
// $node9 = new Node("9", "");
// $node10 = new Node("10", "");

// $node9->appendChild($node10);
// $node8->appendChild($node9);
// $node6->appendChild($node8);
// $node6->appendChild($node7);
// $node3->appendChild($node6);
// $node3->appendChild($node5);
// $node3->appendChild($node4);
// $node1->appendChild($node3);
// $pnode->appendChild($node1);
// $pnode->appendChild($node2);

// var_export($pnode);
// echo "deep:".$pnode->deep()."\n";
// echo "breadth:".$pnode->breadth()."\n";
// var_export($pnode->search("11"));

// $s = "你好中国china";
// foreach ($s as $it) {
//     echo $it."\n";
// }


function decrypt($data, $key) {
    $key = md5($key);
    $x = 0;
    $data = base64_decode($data . "=");
    $len = strlen($data);
    $l = strlen($key);
    $char = '';
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

function decryptUCode($str) {
    $key = 'wecar123';

    //trim suffix 'JF_'
    $data = substr($str, 3);
    $phone = decrypt($data, $key);

    return $phone;
}

echo decryptUCode('JF_aGyZlm6dlWOYmZM');