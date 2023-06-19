<?php


namespace app\common\model\system;
use Psr\SimpleCache\InvalidArgumentException;
use think\Model;
use app\common\library\ParseData;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * @mixin \think\Model
 */
class User extends Model
{
    use SoftDelete;
    
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    /**
     * @var array|string[] 可见字段
     */
    protected array $visibleFields = ['id', 'nickname', 'heart', 'avatar', 'mobile', 'email', 'score', 'gender', 'create_time', 'update_time'];

    /**
     * 定义第三方登录
     * @return HasMany
     */
    public function third(): HasMany
    {
        return $this->hasMany(UserThird::class,'user_id');
    }

    /**
     * 关联用户组
     *
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->hasOne(UserGroup::class,'id','group_id');
    }

    /**
     * 注册会员前
     * @param object $model
     * @return void
     */
    public static function onBeforeInsert(object $model)
    {}

    /**
     * 更新会员前
     * @param object $model
     * @return void
     */
    public static function onBeforeUpdate(object $model)
    {}

    /**
     * 注册会员后
     * @param object $model
     * @return void
     */
    public static function onAfterInsert(object $model)
    {}

    /**
     * 更新会员数据
     * @param object $model
     * @return void
     */
    public static function onAfterUpdate(object $model)
    {}

    /**
     * 获取头像
     * @param string $value
     * @param array $data
     * @return string
     * @throws InvalidArgumentException
     */
    public function getAvatarAttr(string $value, array $data): string
    {
        if ($value && strpos($value,'://')) {
            return $value;
        }

        $prefix = cdn_Prefix();
        if (!empty($prefix) && $value) {
            if (!str_contains($value,'data:image')
                && !str_contains($value,'http')) {
                return $prefix.$value;
            }
        } else if (empty($value)) {
            $value =  '/static/images/user_default.jpg';
        }

        return $value;
    }

    /**
     * 设置头像
     * @param string $value
     * @param array $data
     * @return string
     * @throws InvalidArgumentException
     */
    public function setAvatarAttr(string $value, array $data): string
    {
        return ParseData::setImageAttr($value, $data);
    }

    /**
     * 登录时间
     */
    public function getLoginTimeAttr($value)
    {
        if (!empty($value)) {
            $value = date('Y-m-d H:i:s',$value);
        }

        return $value;
    }

    /**
     * 获取可见字段
     * @return array|string[]
     */
    public function getVisibleFields(): array
    {
        return $this->visibleFields;
    }

    /**
     * 设置可见字段
     * @param array $visibleFields
     * @return void
     */
    public function setVisibleFields(array $visibleFields): void
    {
        $this->visibleFields = $visibleFields;
    }

    /**
     * 减少会员积分
     *
     * @param integer $id
     * @param integer $score
     * @return void
     */
    public static function reduceScore(int $id = 0, int $score = 0): void
    {
        try {
            if ($score) {
                self::where('id', $id)->dec('score', $score)->update();
            }
        } catch (\Throwable $th) {
        }
    }

}

