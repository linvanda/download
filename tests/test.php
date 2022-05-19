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


$s = 'a:10:{s:6:"titles";a:2:{i:0;s:36:"储值卡新开卡用户统计汇总";i:1;s:30:"储值卡新开卡用户统计";}s:9:"summaries";N;s:7:"headers";a:2:{i:0;a:4:{s:12:"油站名称";s:24:"中国钓鱼岛加油站";s:12:"开始时间";s:19:"2022-05-09 00:00:00";s:12:"结束时间";s:19:"2022-05-17 23:59:59";s:6:"单位";s:3:"元";}i:1;a:4:{s:12:"油站名称";s:24:"中国钓鱼岛加油站";s:12:"开始时间";s:19:"2022-05-09 00:00:00";s:12:"结束时间";s:19:"2022-05-17 23:59:59";s:6:"单位";s:3:"元";}}s:7:"footers";a:2:{i:0;a:3:{s:9:"制表人";s:21:"智慧油站管理员";s:12:"制单时间";s:19:"2022-05-19 10:17:27";s:6:"签字";s:0:"";}i:1;a:3:{s:9:"制表人";s:21:"智慧油站管理员";s:12:"制单时间";s:19:"2022-05-19 10:17:27";s:6:"签字";s:0:"";}}s:13:"headers_align";a:1:{i:0;s:5:"right";}s:13:"footers_align";a:1:{i:0;s:5:"right";}s:9:"templates";a:2:{i:0;O:36:"App\Domain\Target\Template\Excel\Tpl":2:{s:41:" App\Domain\Target\Template\Excel\Tpl col";O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:5:"_top_";s:8:" * title";s:0:"";s:6:" * pos";a:2:{i:0;i:0;i:1;i:0;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:5:{i:0;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:8:"TotalQty";s:8:" * title";s:12:"首充总数";s:6:" * pos";a:2:{i:0;i:1;i:1;i:0;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:1;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:5:"KHSUM";s:8:" * title";s:12:"开卡总数";s:6:" * pos";a:2:{i:0;i:1;i:1;i:1;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:2;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:13:"SUMCountMoney";s:8:" * title";s:15:"充值总金额";s:6:" * pos";a:2:{i:0;i:1;i:1;i:2;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:3;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:10:"SUMKZMoney";s:8:" * title";s:12:"首充本金";s:6:" * pos";a:2:{i:0;i:1;i:1;i:3;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:4;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:11:"SUMSKJMoney";s:8:" * title";s:12:"首充赠金";s:6:" * pos";a:2:{i:0;i:1;i:1;i:4;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}}}s:41:" App\Domain\Target\Template\Excel\Tpl row";N;}i:1;O:36:"App\Domain\Target\Template\Excel\Tpl":2:{s:41:" App\Domain\Target\Template\Excel\Tpl col";O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:5:"_top_";s:8:" * title";s:0:"";s:6:" * pos";a:2:{i:0;i:0;i:1;i:0;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:14:{i:0;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:6:"KHDate";s:8:" * title";s:12:"开卡时间";s:6:" * pos";a:2:{i:0;i:1;i:1;i:0;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:1;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:13:"KHStationName";s:8:" * title";s:12:"开卡油站";s:6:" * pos";a:2:{i:0;i:1;i:1;i:1;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:2;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:6:"CardNo";s:8:" * title";s:6:"卡号";s:6:" * pos";a:2:{i:0;i:1;i:1;i:2;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:3;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:10:"CardNumber";s:8:" * title";s:12:"卡面卡号";s:6:" * pos";a:2:{i:0;i:1;i:1;i:3;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:4;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:5:"Phone";s:8:" * title";s:9:"手机号";s:6:" * pos";a:2:{i:0;i:1;i:1;i:4;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:5;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:6:"CZDate";s:8:" * title";s:12:"首充时间";s:6:" * pos";a:2:{i:0;i:1;i:1;i:5;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:6;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:13:"CZStationName";s:8:" * title";s:12:"首充油站";s:6:" * pos";a:2:{i:0;i:1;i:1;i:6;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:7;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:10:"CountMoney";s:8:" * title";s:12:"首充金额";s:6:" * pos";a:2:{i:0;i:1;i:1;i:7;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:8;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:7:"KZMoney";s:8:" * title";s:12:"首充本金";s:6:" * pos";a:2:{i:0;i:1;i:1;i:8;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:9;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:8:"SKJMoney";s:8:" * title";s:12:"首充赠金";s:6:" * pos";a:2:{i:0;i:1;i:1;i:9;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:10;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:9:"CarNumber";s:8:" * title";s:9:"车牌号";s:6:" * pos";a:2:{i:0;i:1;i:1;i:10;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:11;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:8:"CardName";s:8:" * title";s:9:"卡名称";s:6:" * pos";a:2:{i:0;i:1;i:1;i:11;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:12;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:11:"CompanyName";s:8:" * title";s:12:"车队名称";s:6:" * pos";a:2:{i:0;i:1;i:1;i:12;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}i:13;O:40:"App\Domain\Target\Template\Excel\ColHead":6:{s:11:" * dataType";s:6:"string";s:7:" * name";s:13:"CustomerGroup";s:8:" * title";s:12:"所在卡组";s:6:" * pos";a:2:{i:0;i:1;i:1;i:13;}s:8:" * style";O:38:"App\Domain\Target\Template\Excel\Style":6:{s:45:" App\Domain\Target\Template\Excel\Style width";i:0;s:46:" App\Domain\Target\Template\Excel\Style height";i:0;s:45:" App\Domain\Target\Template\Excel\Style color";s:0:"";s:47:" App\Domain\Target\Template\Excel\Style bgColor";s:0:"";s:45:" App\Domain\Target\Template\Excel\Style align";s:6:"center";s:44:" App\Domain\Target\Template\Excel\Style bold";b:0;}s:11:" * children";a:0:{}}}}s:41:" App\Domain\Target\Template\Excel\Tpl row";N;}}s:10:"multi_type";s:4:"page";s:13:"default_width";i:16;s:14:"default_height";i:18;}';
$o = unserialize($s);
var_export($o['summaries'] === null);
