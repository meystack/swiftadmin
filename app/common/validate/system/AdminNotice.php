<?php
declare (strict_types=1);

namespace app\common\validate\system;

use app\admin\enums\AdminNoticeEnum;
use think\Validate;
use app\common\model\system\Admin as AdminModel;

class AdminNotice extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'admin_id' => 'require|checkUser',
        'send_id'  => 'checkReceive',
        'type'     => 'require',
        'content'  => 'require',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'admin_id.require'     => '请选择用户',
        'admin_id.checkUser'   => '用户不存在',
        'type.require'         => '请选择消息类型',
        'content.require'      => '消息内容不能为空',
        'send_id.checkReceive' => '不能给自己发送消息',
    ];

    // 测试验证场景
    protected $scene = [];

    /**
     * 验证用户组权限
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function checkUser($value, $rule, $data): bool
    {
        $result = AdminModel::where('id', $value)->findOrEmpty()->toArray();
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function checkReceive($value, $rule, $data): bool
    {
        if ($data['type'] == AdminNoticeEnum::MESSAGE && $value == $data['admin_id']) {
            return false;
        }

        return true;
    }
}
