<?php

// include "./base.php";

// $redis = new \Redis();
// $redis->connect("localhost", 6380);

// $redis->psubscribe(['__key*__:*'], function ($redis, $chan, $msg) {
//     echo "get msg from $chan -- $msg\n";
// });

// $redis->publish('news.sport', "hello client sport");

// $res = $redis->brPop("mylist1", 10);

// var_export($res);

// $sentinel = new \RedisSentinel('127.0.0.1', 26379);

// $res = $sentinel->ckquorum('mymaster');
// var_export($res);
// $res = $redis->rawCommand("SENTINEL", "masters");

// $master = $redis->rawCommand("SENTINEL", 'master', 'mymaster');

// $cluster = new \RedisSentinel();


// var_export($master);

// $s = '123';
// $r = 0;

// for ($i = 0; $i < strlen($s); $i++) {
//     $r = $r * 10 + (ord($s[$i]) - ord('0'));
// }

// echo $r === 123;


// echo crc32("add");

// $cluster = new RedisCluster(NULL, Array("127.0.0.1:7000", "127.0.0.1:7001"), 1.5, 1.5);
$t = "你好\n换行";
echo strpos($t, "\n");