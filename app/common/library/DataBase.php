<?php
declare (strict_types = 1);

namespace app\common\library;

use support\Log;
use think\facade\Db;

/**
 * 数据库操作类
 */
class DataBase {

    /**
     * 导入目录下Install.sql文件
     * @param string $sqlPath
     * @throws \Exception
     */
    public static function importSql(string $sqlPath)
    {
        if (is_file($sqlPath)) {
            $sql = file_get_contents($sqlPath);
            $sqlRecords = str_ireplace("\r", "\n", $sql);
            $sqlRecords = explode(";\n", $sqlRecords);
            $sqlRecords = str_replace("__PREFIX__", getenv('DATABASE_PREFIX'), $sqlRecords);
            foreach ($sqlRecords as $line) {
                if (empty($line)) {
                    continue;
                }
                try {
                    Db::getPdo()->exec($line);
                } catch (\Throwable $th) {
                    Log::info($th->getMessage());
                }
            }
        }
    }

    /**
     * 获取数据库文件表名
     * @param string $sqlFile
     * @return array
     */
    public static function getSqlTables(string $sqlFile): array
    {
        $regex = "/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z_]+)`?/mi";
        $tables = [];
        if (is_file($sqlFile)) {
            preg_match_all($regex, file_get_contents($sqlFile), $matches);
            if (isset($matches[2])) {
                foreach ($matches[2] as $match) {
                    $tables[] = str_replace('__PREFIX__', getenv('DATABASE_PREFIX'), $match);
                }
            }
        }

        return $tables;
    }
}