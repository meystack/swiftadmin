<?php
declare (strict_types=1);

namespace app\common\model\system;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class AdminRules extends Model
{
    use SoftDelete;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 获取无限极分类
     * @access public static
     * @param string $tips 名称格式
     * @param int $pid 栏目父ID
     * @param array $array 引用数组
     * @param int $blank 替换字符
     * @param int $level 栏目等级
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getListRule(string $tips = '', int $pid = 0, array &$array = [], int $blank = 0, int $level = 0): array
    {
        $result = self::where('pid', $pid)->select()->toArray();
        foreach ($result as $key => $value) {
            if (!empty($tips)) {
                $cateName = $tips . $value['title'];
                $value['title'] = str_repeat('', $blank) . $cateName;
            }
            $value['_level'] = $level;
            $array[] = $value;
            unset($result[$key]);
            self::getListRule($tips, $value['id'], $array, $blank + 1, $level + 1);
        }

        return $array;
    }

    /**
     * 返回栏目树形结构
     * @return array|void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getListTree()
    {
        $array = self::field('*,title as name')->order('sort asc')->select()->toArray();
        foreach ($array as $key => $value) {
            $array[$key]['name'] = __($value['name']);
            $array[$key]['title'] = __($value['title']);
        }
        if (is_array($array) && !empty($array)) {
            return list_to_tree($array);
        }
    }

    /**
     * 递归创建菜单
     * @param array $list 菜单列表
     * @param string $note
     * @param mixed $parent 父类的name或pid
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function createMenu(array $list = [], string $note = '',mixed $parent = 0)
    {
        $fields = array_flip(['title', 'router', 'alias', 'type', 'icon', 'note', 'status']);
        foreach ($list as $key => $item) {
            $data = array_intersect_key($item, $fields);
            $data['pid'] = $parent;
            $children = isset($item['children']) && $item['children'];
            $data['type'] = $children ? 0 : $item['type'] ?? 1;
            $data['note'] = $note;
            $data['auth'] = $item['auth'] ?? 1;
            $data['isSystem'] = $item['isSystem'] ?? 0;
            $data['sort'] = $item['sort'] ?? self::count() + 1;
            $data['alias'] = substr(str_replace('/', ':', $data['router']), 1);
            $result = self::withTrashed()->where(['note' => $data['note'], 'pid' => $data['pid'], 'router' => $data['router']])->find();
            if (empty($result)) {
                $result = self::create($data);
            } else {
                $result->where('id', $result->id)->update($data);
            }
            if ($children) {
                self::createMenu($item['children'], $note, $result['id']);
            }
        }
    }

    /**
     * 启用菜单
     * @param string $name
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function enabled(string $name): void
    {
        $list = self::getNoteByMenus($name);
        foreach ($list as $item) {
            $item->restore();
        }
    }

    /**
     * 禁用菜单
     * @param string $name
     * @param bool $force
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function disabled(string $name, bool $force = false): void
    {
        $list = self::getNoteByMenus($name);
        foreach ($list as $item) {
            self::destroy($item['id'], $force);
        }
    }

    /**
     * 导出指定名称的菜单规则
     * @param string $name
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function export(string $name): array
    {
        $list = self::field('id,pid,title,router,icon,auth,type')->where('note', $name)->order('sort asc')->select()->toArray();
        return self::parseMenuChildren(list_to_tree($list));
    }

    /**
     * 解析菜单/子菜单
     * @param array $list
     * @return array
     */
    protected static function parseMenuChildren(array $list): array
    {
        foreach ($list as $key => $value) {
            unset($list[$key]['id']);
            unset($list[$key]['pid']);
            if (isset($value['children'])) {
                $list[$key]['children'] = self::parseMenuChildren($value['children']);
            }
        }

        return $list;
    }

    /**
     * 根据名称获取规则IDS
     * @param string $name
     * @return object|mixed|void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getNoteByMenus(string $name = '')
    {
        return self::withTrashed()->where('note', $name)->select();
    }

    /**
     * 字段修改器
     *
     * @param [type] $value
     * @return void
     */
    public function setSortAttr($value)
    {
        if (is_empty($value)) {
            return self::max('id') + 1;
        }
        return $value;
    }

}
