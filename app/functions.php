<?php
/**
 * 全局公共函数库
 */

use think\facade\Cache;
use app\common\model\system\Config;
use think\helper\Str;

// 权限常量
const AUTH_CATE = 'cates';
const AUTH_RULES = 'rules';
// +----------------------------------------------------------------------
// | 常规助手函数
// +----------------------------------------------------------------------
if (!function_exists('input')) {
    /**
     * 过滤函数
     * @param string $key
     * @param null $default
     * @return mixed
     */
    function input(string $key = '', $default = null)
    {
        return \request()->input($key, $default);
    }
}

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param $event
     * @param mixed $params
     * @param bool $array
     * @return mixed
     */
    function hook($event, $params = '', bool $array = true)
    {
        $result = \webman\Event\Event::emit($event, $params, true);
        return $array ? $result : join('', $result);
    }
}

if (!function_exists('halt')) {
    /**
     * 调试变量并且中断输出
     * @param mixed $vars 调试变量或者信息
     * @throws Exception
     */
    function halt(...$vars)
    {
        throw new \Exception(...$vars);
    }
}

/**
 * @param $config
 * @return string
 */
if (!function_exists('captcha_src')) {
    /**
     * 获取验证码图片地址
     * @param $config
     * @return string
     */
    function captcha_src($config = null): string
    {
        return '/captcha' . ($config ? "/{$config}" : '');
    }
}

if (!function_exists('url')) {
    /**
     * 生成URL地址
     * @param string $url
     * @param array $vars
     * @param string $app
     * @return string
     */
    function url(string $url, array $vars = [], string $app = ''): string
    {
        $app = $app ?: request()->app;
        $vars = !empty($vars) ? '?' . http_build_query($vars) : '';

        if (!Str::startsWith($url, '/')) {
            $url = DIRECTORY_SEPARATOR . $url;
        }

        return $app == 'index' ? $url . $vars : '/' . $app . $url . $vars;
    }
}

if (!function_exists('token')) {
    /**
     * 获取Token令牌
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     */
    function token(string $name = '__token__', string $type = 'md5'): string
    {
        try {
            return \request()->buildToken($name, $type);
        } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
        }

        return '';
    }
}

if (!function_exists('token_field')) {
    /**
     * 生成令牌隐藏表单
     * @param string $name 令牌名称
     * @param mixed $type 令牌生成方法
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function token_field(string $name = '__token__', string $type = 'md5'): string
    {
        $token = \request()->buildToken($name, $type);
        return '<input type="hidden" name="' . $name . '" value="' . $token . '" />';
    }
}

// +----------------------------------------------------------------------
// | 文件操作函数开始
// +----------------------------------------------------------------------
if (!function_exists('read_file')) {
    /**
     * 获取文件内容
     * @param string $file 文件路径
     * @return false|string content
     */
    function read_file(string $file)
    {
        return !is_file($file) ? '' : @file_get_contents($file);
    }
}

if (!function_exists('root_path')) {
    /**
     * 获取项目根目录
     * @param string $string
     * @return string
     */
    function root_path(string $string = ''): string
    {
        $base = str_replace('\\', '/', realpath(__DIR__ . '/../'));
        return $string ? $base . '/' . $string . '/' : $base . '/';
    }
}

if (!function_exists('write_file')) {
    /**
     * 数据写入文件
     * @param $file
     * @param $content
     * @return false|int
     */
    function write_file($file, $content)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mk_dirs($dir);
        }
        return @file_put_contents($file, $content);
    }
}

if (!function_exists('copy_file')) {
    /**
     * 复制文件
     * @param string $src
     * @param string $dst
     * @return bool
     */
    function copy_file(string $src, string $dst): bool
    {
        $dir = dirname($dst);
        if (!is_dir($dir)) {
            mk_dirs($dir);
        }

        return @copy($src, $dst);
    }
}

if (!function_exists('mk_dirs')) {
    /**
     * 递归创建文件夹
     * @param $path
     * @param int $mode 文件夹权限
     * @return bool
     */
    function mk_dirs($path, int $mode = 0777): bool
    {
        if (!is_dir(dirname($path))) {
            mk_dirs(dirname($path));
        }

        if (!file_exists($path)) {
            return mkdir($path, $mode);
        }

        return true;
    }
}

if (!function_exists('arr2file')) {
    /**
     * 数组写入文件
     * @param string $file 文件路径
     * @param array $array 数组数据
     * @return false|int
     */
    function arr2file(string $file, $array = '')
    {
        if (is_array($array)) {
            $cont = var_exports($array);
        } else {
            $cont = $array;
        }
        $cont = "<?php\nreturn $cont;";
        return write_file($file, $cont);
    }
}

if (!function_exists('arr2router')) {
    /**
     * 数组写入路由文件
     * @param string $file 文件路径
     * @param array $array
     * @return false|int
     */
    function arr2router(string $file, array $array = [])
    {
        if (is_array($array)) {
            $cont = var_exports($array);
        } else {
            $cont = $array;
        }
        $cont = "<?php\nuse think\\facade\\Route;\n\n$cont";
        return write_file($file, $cont);
    }
}

if (!function_exists('var_exports')) {
    /**
     * 数组语法(方括号)
     * @param array $expression 数组
     * @param bool $return 返回类型
     * @return string
     */
    function var_exports(array $expression, bool $return = true)
    {
        $export = var_export($expression, true);
        $patterns = [
            "/array \(/"                       => '[',
            "/^([ ]*)\)(,?)$/m"                => '$1]$2',
            "/=>[ ]?\n[ ]+\[/"                 => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];

        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ($return) {
            return $export;
        } else {
            echo $export;
        }

        return false;
    }
}


if (!function_exists('recursive_delete')) {
    /**
     * 递归删除目录
     */
    function recursive_delete($dir)
    {
        // 打开指定目录
        if ($handle = @opendir($dir)) {

            while (($file = readdir($handle)) !== false) {
                if (($file == ".") || ($file == "..")) {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) { // 递归
                    recursive_delete($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file); // 删除文件
                }
            }

            @closedir($handle);
            @rmdir($dir);
        }
    }
}

if (!function_exists('traverse_scanDir')) {
    /**
     * 递归遍历文件夹
     * @param bool $bool 是否递归
     * @param string $dir 文件夹路径
     * @return array
     */
    function traverse_scanDir(string $dir, bool $bool = true): array
    {
        $array = [];
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            # code...
            if ($file != '.' && $file != '..') {
                $child = $dir . '/' . $file;
                if (is_dir($child) && $bool) {
                    $array[$file] = traverse_scanDir($child);
                } else {
                    $array[] = $file;
                }
            }
        }

        return $array;
    }
}

// +----------------------------------------------------------------------
// | 字符串函数开始
// +----------------------------------------------------------------------
//
if (!function_exists('release')) {

    /**
     * 获取静态版本
     * @return int|mixed
     */
    function release()
    {
        return getenv('APP_DEBUG') ? \system\Random::alphaNum() : config('app.version');
    }
}

if (!function_exists('delNr')) {
    /**
     * 去掉换行
     * @param string $str 字符串
     * @return string
     */
    function delNr(string $str): string
    {
        $str = str_replace(array("<nr/>", "<rr/>"), array("\n", "\r"), $str);
        return trim($str);
    }
}

if (!function_exists('delNt')) {
    /**
     * 去掉连续空白
     * @param string $str 字符串
     * @return string
     */
    function delNt(string $str): string
    {
        $str = str_replace("　", ' ', str_replace("", ' ', $str));
        $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
        return trim($str);
    }
}

if (!function_exists('msubstr')) {
    /**
     * 字符串截取(同时去掉HTML与空白)
     * @param string $str
     * @param int $start
     * @param int $length
     * @param string $charset
     * @param bool $suffix
     * @return string
     */
    function msubstr(string $str, int $start = 0, int $length = 100, string $charset = "utf-8", bool $suffix = true): string
    {

        $str = preg_replace('/<[^>]+>/', '', preg_replace("/[\r\n\t ]{1,}/", ' ', delNt(strip_tags($str))));
        $str = preg_replace('/&(\w{4});/i', '', $str);

        // 直接返回
        if ($start == -1) {
            return $str;
        }

        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);

        } else {
            $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
            $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
            $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
            $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }

        $fix = '';
        if (strlen($slice) < strlen($str)) {
            $fix = '...';
        }
        return $suffix ? $slice . $fix : $slice;
    }
}

if (!function_exists('cdn_Prefix')) {

    /**
     * 获取远程图片前缀
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function cdn_Prefix()
    {
        return saenv('upload_http_prefix');
    }
}

if (!function_exists('pinyin')) {
    /**
     * 获取拼音
     * @param $chinese
     * @param bool $onlyFirst
     * @param string $delimiter
     * @param bool $ucFirst
     * @return string
     */
    function pinyin($chinese, bool $onlyFirst = false, string $delimiter = '', bool $ucFirst = false): string
    {
        $pinyin = new Overtrue\Pinyin\Pinyin();

        if ($onlyFirst) {
            $result = $pinyin->abbr($chinese, $delimiter);
        } else {
            $result = $pinyin->permalink($chinese, $delimiter);
        }

        if ($ucFirst) {
            $pinyinArr = explode($delimiter, $result);
            $result = implode($delimiter, array_map('ucfirst', $pinyinArr));
        }

        return $result;
    }
}


if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int $size 大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes(int $size, string $delimiter = ' '): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('hide_str')) {
    /**
     * 将一个字符串部分字符用*替代隐藏
     * @param string $string 待转换的字符串
     * @param int $begin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int $len 需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int $type 转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串中间用***代替
     * @param string $glue 分割符
     * @return string   处理后的字符串
     */
    function hide_str(string $string, int $begin = 3, int $len = 4, int $type = 0, string $glue = "@")
    {
        if (empty($string)) {
            return false;
        }

        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", $array);
        } elseif ($type == 1) {
            $array = array_reverse($array);
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", array_reverse($array));
        } elseif ($type == 2) {
            $array = explode($glue, $string);
            if (isset($array[0])) {
                $array[0] = hide_str($array[0], $begin, $len, 1);
            }
            $string = implode($glue, $array);
        } elseif ($type == 3) {
            $array = explode($glue, $string);
            if (isset($array[1])) {
                $array[1] = hide_str($array[1], $begin, $len, 0);
            }
            $string = implode($glue, $array);
        } elseif ($type == 4) {
            $left = $begin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i])) {
                    $tem[] = $i >= $left ? "" : $array[$i];
                }
            }
            $tem[] = '*****';
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                if (isset($array[$i])) {
                    $tem[] = $array[$i];
                }
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}

// +----------------------------------------------------------------------
// | 系统APP函数开始
// +----------------------------------------------------------------------
//
if (!function_exists('__')) {
    /**
     * 全局多语言函数
     */
    function __($str, $parameters = [], $domain = null, $locale = null)
    {
        if (is_numeric($str)
            || getenv('LANG_DEFAULT_LANG') === 'zh-CN') {
            return $str;
        }

        return trans($str, $parameters, $domain, $locale);
    }
}

if (!function_exists('saenv')) {
    /**
     * 获取系统配置信息
     * @param string $name
     * @param bool $group
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    function saenv(string $name, bool $group = false)
    {

        $redis = 'sys_' . $name;
        try {

            $config = Cache::get($redis);
            if (empty($config) || getenv('APP_DEBUG')) {
                $config = Config::all($name, $group);
                Cache::set($redis, $config);
            }
        } catch (\Throwable $th) {
            return [];
        }

        // 优先返回组配置
        if (!empty($group)) {
            return $config;
        } else {
            if (isset($config[$name]) && $config[$name]) {
                return $config[$name];
            }
        }

        return false;
    }
}

if (!function_exists('system_cache')) {
    /**
     * 全局缓存控制函数
     * @param string|null $name
     * @param null $options
     * @param null $tag
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function system_cache(string $name = null, $value = '', $options = null, $tag = null)
    {
        // 调试模式关闭缓存
        if (getenv('APP_DEBUG') || !saenv('cache_status')) {
            return false;
        }

        if (is_null($name)) {
            return [];
        }

        if ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? Cache::has(substr($name, 1)) : Cache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return Cache::delete($name);
        }

        // 缓存数据
        if (is_array($options)) {
            $expire = $options['expire'] ?? null;
        } else {
            $expire = $options;
        }

        if (is_null($tag)) {
            return Cache::set($name, $value, $expire);
        } else {
            return Cache::tag($tag)->set($name, $value, $expire);
        }
    }
}

if (!function_exists('system_reload')) {
    /**
     * 重载系统
     * return bool
     */
    function system_reload(): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return false;
        }
        if (function_exists('posix_kill')
            && function_exists('posix_getppid')) {
            posix_kill(posix_getppid(), 10);
            return true;
        }
        return false;
    }
}

if (!function_exists('parse_array_ini')) {
    /**
     * 解析数组到ini文件
     * @param array $array 数组
     * @param string $content 字符串
     * @return string    返回一个ini格式的字符串
     */
    function parse_array_ini(array $array, string $content = ''): string
    {

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // 分割符PHP_EOL
                $content .= PHP_EOL . '[' . $key . ']' . PHP_EOL;
                foreach ($value as $field => $data) {
                    $content .= $field . ' = ' . $data . PHP_EOL;
                }

            } else {
                $content .= $key . ' = ' . $value . PHP_EOL;
            }
        }

        return $content;
    }
}

if (!function_exists('list_search')) {
    /**
     * 从数组查找数据返回
     * @param array $list 原始数据
     * @param array $condition 规则['id'=>'??']
     * @return mixed
     */
    function list_search(array $list, array $condition)
    {
        if (is_string($condition)) {
            parse_str($condition, $condition);
        }
        // 返回的结果集合
        $resultSet = array();
        foreach ($list as $key => $data) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($data[$field])) {
                    if (0 === strpos($value, '/')) {
                        $find = preg_match($value, $data[$field]);
                    } else if ($data[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find)
                $resultSet[] = &$list[$key];
        }

        if (!empty($resultSet[0])) {
            return $resultSet[0];
        } else {
            return false;
        }
    }
}

if (!function_exists('list_to_tree')) {
    /**
     * 根据ID和PID返回一个树形结构
     * @param array $list 数组结构
     * @param string $id
     * @param string $pid
     * @param string $child
     * @param int $level
     * @return mixed
     */
    function list_to_tree(array $list, string $id = 'id', string $pid = 'pid', string $child = 'children', int $level = 0): array
    {
        // 创建Tree
        $tree = $refer = array();
        if (is_array($list)) {

            // 创建基于主键的数组引用
            foreach ($list as $key => $data) {
                $refer[$data[$id]] = &$list[$key];
            }

            foreach ($list as $key => $data) {

                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($level == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }
}

if (!function_exists('list_sort_by')) {
    /**
     *----------------------------------------------------------
     * 对查询结果集进行排序
     *----------------------------------------------------------
     * @access public
     *----------------------------------------------------------
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * @switch string  asc正向排序 desc逆向排序 nat自然排序
     *----------------------------------------------------------
     * @return mixed
     *----------------------------------------------------------
     */
    function list_sort_by(array $list, string $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }
        return false;
    }
}

if (!function_exists('is_empty')) {
    /**
     * 判断是否为空值
     * @param array|string $value 要判断的值
     * @return bool
     */
    function is_empty($value): bool
    {
        if (!isset($value)) {
            return true;
        }

        if (trim($value) === '') {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_mobile')) {

    /**
     * 验证输入的手机号码
     * @access  public
     * @param $mobile
     * @return bool
     */
    function is_mobile($mobile): bool
    {
        // 正则表达式判断手机号
        if (preg_match('/^1[3456789]\d{9}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}

// +----------------------------------------------------------------------
// | 数据加密函数开始
// +----------------------------------------------------------------------
if (!function_exists('encryptPwd')) {
    /**
     * hash - 密码加密
     */
    function encryptPwd($pwd, $salt = 'swift', $encrypt = 'md5')
    {
        return $encrypt($pwd . $salt);
    }
}

// +----------------------------------------------------------------------
// | 时间相关函数开始
// +----------------------------------------------------------------------
if (!function_exists('linux_extime')) {
    /**
     * 获取某天前时间戳
     * @param  $day
     * @return int
     */
    function linux_extime($day): int
    {
        $day = intval($day);
        return mktime(23, 59, 59, intval(date("m")), intval(date("d")) - $day, intval(date("y")));
    }
}

if (!function_exists('today_seconds')) {
    /**
     * 返回今天还剩多少秒
     * @return int
     */
    function today_seconds(): int
    {
        $mtime = mktime(23, 59, 59, intval(date("m")), intval(date("d")), intval(date("y")));
        return $mtime - time();
    }
}


if (!function_exists('is_today')) {
    /**
     * 判断当前是否为当天时间
     * @param $time
     * @return bool
     */
    function is_today($time): bool
    {

        if (!$time) {
            return false;
        }

        $today = date('Y-m-d');
        if (strstr($time, '-')) {
            $time = strtotime($time);
        }

        if ($today == date('Y-m-d', $time)) {
            return true;
        } else {
            return false;
        }
    }
}

// +----------------------------------------------------------------------
// | 系统安全函数开始
// +----------------------------------------------------------------------
if (!function_exists('request_validate_rules')) {
    /**
     * 自动请求验证规则
     * @param array $data               POST数据
     * @param string $validateClass     验证类名
     * @param string $validateScene     验证场景
     * @return mixed
     */
    function request_validate_rules(array $data = [], string $validateClass = '', string $validateScene = '')
    {
        if (!empty($validateClass)) {
            if (!preg_match('/app\x{005c}(.*?)\x{005c}/', $validateClass, $match)) {
                $validateClass = '\\app\\common\\validate\\' . ucfirst($validateClass);
            } else {
                $validateClass = str_replace("\\model\\", "\\validate\\", $validateClass);
            }
            try {
                if (class_exists($validateClass)) {
                    $validate = new $validateClass;
                    if (!$validate->scene($validateScene)->check($data)) {
                        return $validate->getError();
                    }
                }
            } catch (Throwable $th) {
                return $th->getMessage();
            }
        }

        return $data;
    }
}

if (!function_exists('check_user_third')) {
    /**
     * 获取第三方登录
     * @param mixed $type
     * @param int $id
     * @return bool
     */
    function check_user_third($type, $id = 0): bool
    {
        if (!$id || !$type) {
            return false;
        }

        if (\app\common\model\system\UserThird::where('user_id', $id)->getByType($type)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('distance_day')) {
    /**
     * 计算天数
     * @param mixed $time
     * @return false|float
     */
    function distance_day($time = '')
    {

        if (!$time) {
            return false;
        }

        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $time = time() - $time;
        return ceil($time / (60 * 60 * 24));
    }
}

if (!function_exists('request_error')) {
    /**
     * 返回错误模板
     * @param string $app
     * @param string $code
     * @return string
     */
    function request_error(string $app = 'index',string $code = '404'): string
    {
        switch ($app){
            case 'admin':
                $exception = config('app.exception_template');
                $_file = $exception[$code] ?? $exception['500'];
                break;
            default:
                $_file = public_path() . DIRECTORY_SEPARATOR . $code . '.html';
                break;
        }
        return is_file($_file) ? file_get_contents($_file) : $code.' error';
    }
}

// +----------------------------------------------------------------------
// | 插件服务函数开始
// +----------------------------------------------------------------------
/**
 * 自动加载函数库
 * @param $class
 * @return object
 * @throws Exception
 */
spl_autoload_register(function ($class) {

    $dirs = traverse_scanDir(plugin_path(), false);
    foreach ($dirs as $index => $dir) {
        $functions = plugin_path($dir) . 'function.php';
        if (is_file($functions)) {
            include_once $functions;
        }
    }

    return $class;
});

if (!function_exists('plugin_path')) {
    /**
     * 获取插件目录
     * @param string $string
     * @return string
     */
    function plugin_path(string $string = ''): string
    {
        return $string ? root_path('plugin/' . $string) : root_path('plugin');
    }
}

if (!function_exists('get_api_url')) {
    /**
     * 获取服务器接口
     * @return string
     */
    function get_api_url(): string
    {
        return config('app.api_url');
    }
}

if (!function_exists('get_plugin_query')) {
    /**
     * 查询插件信息
     * @return string
     */
    function get_plugin_query(): string
    {
        return get_api_url() . 'plugin/query';
    }
}

if (!function_exists('get_plugin_class')) {
    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $class 当前类名
     * @return string
     */
    function get_plugin_class(string $name, string $class = ''): string
    {
        $name = trim($name);
        $class = Str::studly(!$class ? $name : $class);
        $namespace = "\\plugin\\" . $name . "\\" . $class;
        return class_exists($namespace) ? $namespace : '';
    }
}

if (!function_exists('get_plugin_instance')) {
    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $class 当前类名
     * @return mixed
     */
    function get_plugin_instance(string $name,string $class = '')
    {
        $object = get_plugin_class($name, $class);
        return $object ? new $object : '';
    }
}

if (!function_exists('get_plugin_list')) {
    /**
     * 获取插件列表
     * @param array $list
     * @param array $other
     * @return array
     */
    function get_plugin_list(array &$list = [], array $other = []): array
    {
        $iterator = glob(plugin_path() . '*', GLOB_ONLYDIR);
        foreach ($iterator as $dir) {
            $name = basename($dir);
            $config = plugin_path($name) . 'config.json';
            if (!is_file($config)) {
                continue;
            }
            try {
                $list[$name] = json_decode(read_file($config), true);
            } catch (\Throwable $th) {
                continue;
            }
        }

        if (!empty($other)) {
            $list = array_merge($list, $other);
        }

        return $list ?: [];
    }
}

if (!function_exists('copydirs')) {
    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs(string $source, string $dest)
    {

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $handle = opendir($source);
        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_dir($source . "/" . $file)) {
                    copydirs($source . "/" . $file, $dest . "/" . $file);
                } else {
                    copy($source . "/" . $file, $dest . "/" . $file);
                }
            }
        }

        closedir($handle);
    }
}

if (!function_exists('remove_empty_dir')) {
    /**
     * 删除空目录
     * @param string $dir 目录
     */
    function remove_empty_dir(string $dir)
    {
        try {
            if (is_dir($dir)) {
                $handle = opendir($dir);
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != "..") {
                        remove_empty_dir($dir . "/" . $file);
                    }
                }

                if (readdir($handle) == false) {
                    @rmdir($dir);
                }

                closedir($handle);
            }
        } catch (\Exception $e) {
        }
    }
}

if (!function_exists('get_plugin_config')) {
    /**
     * 获取插件配置
     * @param string $name 插件名
     * @param bool $force 是否缓存
     * @return array
     * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    function get_plugin_config(string $name, bool $force = false): array
    {
        $array = [];
        $cache = sha1('PLUGIN_' . $name);
        if (!$force || !getenv('APP_DEBUG')) {
            if ($array = Cache::get($cache)) {
                return $array;
            }
        }

        $pluginPath = plugin_path($name);
        $filePath = $pluginPath . 'config.json';
        if (is_file($filePath)) {
            $array = json_decode(read_file($filePath), true);
            if (is_array($array)) {
                $array['path'] = $pluginPath;
                $array['config'] = is_file($pluginPath . 'config.html') ? 1 : 0;
            }
        }

        Cache::set($cache, $array, 86400);
        return $array ?: [];
    }
}

if (!function_exists('set_plugin_config')) {
    /**
     * 设置插件配置
     * @param string $name 插件名
     * @param array $value
     * @return array
     * @throws Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    function set_plugin_config(string $name, array $value): array
    {
        $config = [];
        try {
            $config = get_plugin_config($name, true);
            $config = array_merge($config, $value);
            $filePath = plugin_path($name) . '/config.json';
            write_file($filePath, json_encode($config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            Cache::set(sha1('PLUGIN' . $name), $config);
            plugin_refresh_hooks();
        } catch (Exception $e) {
        }
        return $config;
    }
}

if (!function_exists('get_plugin_menu')) {
    /**
     * 获取前台插件菜单
     * @return void
     */
    function get_plugin_menu()
    {
        $pluginNav = '';
        $pluginList = get_plugin_list();

        foreach ($pluginList as $item) {

            try {

                if (!$item['status']) {
                    continue;
                }

                $file = plugin_path($item['name']) . 'data/menu.html';
                if (is_file($file)) {
                    $pluginNav .= file_get_contents($file) . PHP_EOL;
                }
            } catch (\Throwable $th) {
                continue;
            }
        }

        echo $pluginNav;
    }
}

if (!function_exists('plugin_refresh_hooks')) {
    /**
     * 刷新插件配置
     * @return bool
     */
    function plugin_refresh_hooks(): bool
    {
        $pluginList = get_plugin_list();
        $taglib = [];
        $events = [];
        $routeList = '';
        $parseRules = '<?php' . PHP_EOL . 'return [array' . PHP_EOL . '];';
        foreach ($pluginList as $item) {

            if (!$item['status']) {
                continue;
            }

            $name = $item['name'];
            $rewrite = $item['rewrite'] ?: [];
            foreach ($rewrite as $key => $route) {
                $parse = explode('/', $route);
                $action = end($parse);
                array_pop($parse);
                $path = implode('/', $parse);
                $controller = 'app\\index\\controller\\' . $path;
                if (class_exists($controller) && method_exists($controller, $action)) {
                    $controller = preg_replace('#//#', '/', $controller);
                    $routeList .= PHP_EOL . "   '$key'=>[$controller::class, '$action'],";
                }
            }

            $namespace = '\\plugin\\' . $name . '\\' . ucfirst($name);
            $methods = get_class_methods($namespace);
            $diff_hooks = array_diff($methods, get_class_methods("\\app\\PluginController"));
            foreach ($diff_hooks as $hook) {
                $hookName = $name . '.' . $hook;
                $events[$hook][] = [$namespace, $hook];
            }

            $taglibPath = plugin_path($name) . 'taglib.php';
            if (is_file($taglibPath)) {
                $taglib[] = 'plugin\\' . $name . '\\taglib.php';
            }
        }

        $taglib && arr2file(root_path('config') . 'taglib.php', $taglib);
        $routePath = root_path('config') . 'defineRoute.php';
        write_file($routePath, str_replace('array', $routeList, $parseRules));
        $hooks = include/** @lang text */
        (root_path('config') . 'event.php');

        foreach ($hooks as $key => $item) {
            $separator = explode('.', $key);
            if (current($separator) == 'system') {
                continue;
            }
            if (!array_key_exists($key, $events)) {
                unset($hooks[$key]);
            }
        }

        $eventList = '';
        $events = array_merge($hooks, array_diff_key($events, $hooks));
        foreach ($events as $key => $event) {
            $eventList .= PHP_EOL . "    '$key'=> [";
            foreach ($event as $value) {
                $eventList .= PHP_EOL . "       [" . $value[0] . "::class, '" . $value[1] . "']," . PHP_EOL;
            }

            $eventList .= '     ],';
        }

        $eventPath = root_path('config') . 'event.php';
        write_file($eventPath, str_replace('array', $eventList, $parseRules));
        return system_reload();
    }
}