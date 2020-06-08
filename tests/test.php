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

$flags = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

$s = base_convert("25", 10, 26);
$ss = "";
for($i = 0; $i < strlen($s); $i++) {
    $a = $s[$i];
    if (!is_numeric($a)) {
        $a = ord("j") - 97 + 10;
    }

    $ss .= $flags[$a - 1];
}

echo "---$s---$ss---\n";