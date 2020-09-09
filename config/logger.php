<?php

use WecarSwoole\Util\File;

use function WecarSwoole\Config\apollo;

return [
    'debug' => [
        'file' => File::join(STORAGE_ROOT, 'logs/info.log'),
    ],
    'info' => [
        'file' => File::join(STORAGE_ROOT, 'logs/info.log'),
    ],
    'warning' => [
        'file' => File::join(STORAGE_ROOT, 'logs/warning.log'),
    ],
    'error' => [
        'file' => File::join(STORAGE_ROOT, 'logs/error.log'),
    ],
    'critical' => [
        'mailer' => [
            'driver' => 'default',
            'subject' => '下载中心告警',
            'to' => json_decode(apollo('application', 'logger.emails'), true) ?: []
        ],
        'file' => File::join(EASYSWOOLE_ROOT, 'storage/logs/error.log'),
    ],
    'emergency' => [
        'mailer' => [
            'driver' => 'default',
            'subject' => '下载中心告警',
            'to' => json_decode(apollo('application', 'logger.emails'), true) ?: []
        ],
        'file' => File::join(EASYSWOOLE_ROOT, 'storage/logs/error.log'),
        'sms' => json_decode(apollo('application', 'logger.mobiles'), true) ?: []
    ],
];
