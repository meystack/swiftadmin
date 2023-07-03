<?php
declare (strict_types=1);

namespace app\common\validate\system;

use app\admin\service\AuthService;
use think\Validate;
use app\common\model\system\Admin as AdminModel;

class Admin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'name'     => 'require|min:2|max:12|chsAlphaNum',
        'pwd|密码' => 'require|min:6|max:64',
        'group_id' => 'require|checkGroup',
    ];


    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'name.require'        => '用户名不能为空',
        'name.min'            => '用户名不能少于2个字符',
        'name.max'            => '用户名不能超过12个字符',
        'name.filters'        => '用户名包含禁止注册字符',
        'name.chsAlphaNum'    => '用户名只能是汉字、字母和数字',
        'pwd.require'         => '密码不能为空',
        'pwd.min'             => '密码不能少于6个字符',
        'pwd.max'             => '密码不能超过64个字符',
        'group_id.require'    => '请选择用户组',
        'group_id.checkGroup' => '无权限操作',
    ];

    // 测试验证场景
    protected $scene = [
        'add'   => ['name', 'pwd', 'group_id'],
        'edit'  => ['name', 'group_id'],
        'login' => ['name', 'pwd'],
    ];

    /**
     * 验证用户组权限
     * @param $value
     * @return bool
     */
    protected function checkGroup($value): bool
    {
        $id = request()->get('id', 0);
        $result = AdminModel::where('id', $id)->findOrEmpty()->toArray();
        if (empty($result)) {
            return true;
        }
        $group_id = !empty($value) ? $value . ',' . $result['group_id'] : $result['group_id'];
        $group_id = array_unique(explode(',', $group_id));
        $authService = AuthService::instance();
        if (!$authService->checkRulesForGroup($group_id)) {
            return false;
        }
        return true;
    }
}
