<?php
declare (strict_types=1);

namespace app\common\validate\system;

use app\admin\service\AuthService;
use think\Validate;
use app\common\model\system\AdminGroup as AdminGroupModel;

class AdminGroup extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'id'  => 'require|checkGroup',
        'pid' => 'notEqId',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'pid.notEqId'   => '选择上级分类错误！',
        'id.require'    => '请选择用户组',
        'id.checkGroup' => '无权限操作',
    ];

    protected $scene = [
        'add'  => ['pid'],
        'edit' => ['id', 'pid'],
    ];

    /**
     * 自定义验证规则
     * @param $value
     * @param $rules
     * @param $data
     * @return bool
     */
    protected function notEqId($value, $rules, $data): bool
    {
        if ($value == $data['id']) {
            return false;
        }

        if (!empty($data['id']) && $value > $data['id']) {
            if (AdminGroupModel::getByPid($data['id'])) {
                $this->message['pid.notEqId'] = '禁止修改存在子类的栏目';
                return false;
            }
        }

        return true;
    }

    /**
     * 验证用户组权限
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function checkGroup($value, $rule, $data): bool
    {
        $authService = AuthService::instance();
        $value = explode(',', $value);
        if (!$authService->checkRulesForGroup($value)) {
            return false;
        }

        return true;
    }
}
