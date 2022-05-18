<?php

include "./base.php";

$a = [
    'row' => [
        [
            'title' => '云研发',
            'children' => [
                [
                    'name' => 'front_end',
                    'title' => '前端',
                    'row_count' => 2,
                ],
                [
                    'name' => 'back_end',
                    'title' => '后端',
                    'row_count' => 4,
                ],
            ]
        ],
        [
            'title' => 'OS及智能设备',
            'children' => [
                [
                    'title' => 'OS',
                    'name' => 'os',
                    'row_count' => 3,
                ],
                [
                    'title' => '智能设备',
                    'children' => [
                        [
                            'name' => 'pos',
                            'title' => '手持终端',
                            'row_count' => 2,
                        ],
                        [
                            'name' => 'screen',
                            'title' => '大屏',
                            'row_count' => 3,
                        ],
                    ]
                ]
            ]
        ]
    ],
    'col' => [
        [
            'title' => '人员',
            'children' => [
                [
                    'name' => 'name',
                    'title' => '姓名',
                    'type' => 'string',
                    'color' => 'red',
                    "width" => -1
                ],
                [
                    'title' => '其它',
                    'children' => [
                        [
                            'name' => 'age',
                            'title' => '年龄',
                            'type' => 'number',
                        ],
                        [
                            'name' => 'sex',
                            'title' => '性别',
                            'type' => 'string',
                            'width' => 8,
                        ],
                        [
                            'title' => '爱好',
                            'children' => [
                                [
                                    'name' => 'love_in',
                                    'title' => '室内',
                                ],
                                [
                                    'title' => '室外',
                                    'children' => [
                                        [
                                            'name' => 'love_out_land',
                                            'title' => '陆地',
                                        ],
                                        [
                                            'name' => 'love_out_sky',
                                            'title' => '空中',
                                        ],
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            'title' => '住址',
            'children' => [
                [
                    'name' => 'city',
                    'title' => '城市'
                ],
                [
                    'title' => '小区',
                    'children' => [
                        [
                            'name' => 'area',
                            'title' => '区域',
                        ],
                        [
                            'name' => 'building',
                            'title' => '楼盘',
                        ]
                    ]
                ]
            ]
        ],
    ],
    'col_' => [
        'name' => '名字',
        'age' => '年龄',
        'sex' => '性别',
        'love_in' => '室内爱好',
        'love_out_land' => '室外陆地爱好',
        'love_out_sky' => '室外空中爱好',
        'city' => '城市',
        'area' => '区域',
        'building' => '小区',
    ]
];


$s = '[
[{"name":"张三","age":"1234569987890","love":"爬山","sex":"男"},{"name":"李四","age":16,"love":"滑冰","sex":"男"},{"name":"王五","age":18,"love":"游泳","sex":"女"}],
[{"pay_type":"支付宝","pay_money":200.00,"pay_time":"2020-01-01","goods_name":"矿泉水","_row_head_":"online"},{"pay_type":"微信","pay_money":10.00,"pay_time":"2020-01-01","goods_name":"巧克力","_row_head_":"online"},
"http://localhost:9588/v1/test/source","http://localhost:9588/v1/test/source"
]';
var_export(json_decode($s, true));
