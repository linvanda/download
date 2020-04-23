<?php

namespace App;

use WecarSwoole\ErrCode as BaseErrCode;

/**
 * Class ErrCode
 * 200 表示 OK
 * 500 及以下为框架保留错误码，项目中不要用，项目中从 501 开始
 * @package App
 */
class ErrCode extends BaseErrCode
{
    public const EMPTY_PARAMS = 501; // 缺少参数
    public const PROJ_NOT_EXISTS = 502; // 项目不存在
    public const PROJ_AREADY_EXISTS = 503; // 项目已存在
    public const GROUP_NOT_EXISTS = 504; // 项目组不存在
    public const GROUP_AREADY_EXISTS = 505; // 项目组已经存在
    public const FILE_TYPE_ERR = 506; // 文件类型错误
    public const TASK_NOT_EXISTS = 507; // 任务不存在
    public const TPL_FMT_ERR = 508; // 模板格式不合法
    public const INVALID_STATUS_OP = 509; // 非法的状态切换
}
