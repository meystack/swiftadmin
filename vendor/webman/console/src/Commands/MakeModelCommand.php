<?php

namespace Webman\Console\Commands;

use Doctrine\Inflector\InflectorFactory;
use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Webman\Console\Util;


class MakeModelCommand extends Command
{
    protected static $defaultName = 'make:model';
    protected static $defaultDescription = 'Make model';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Model name');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Type');
        $this->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Select database connection. ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $name = Util::nameToClass($name);
        $type = $input->getArgument('type');
        $connection = $input->getOption('connection');
        $output->writeln("Make model $name");
        if (!($pos = strrpos($name, '/'))) {
            $name = ucfirst($name);
            $model_str = Util::guessPath(app_path(), 'model') ?: 'model';
            $file = app_path() . DIRECTORY_SEPARATOR .  $model_str . DIRECTORY_SEPARATOR . "$name.php";
            $namespace = $model_str === 'Model' ? 'App\Model' : 'app\model';
        } else {
            $name_str = substr($name, 0, $pos);
            if($real_name_str = Util::guessPath(app_path(), $name_str)) {
                $name_str = $real_name_str;
            } else if ($real_section_name = Util::guessPath(app_path(), strstr($name_str, '/', true))) {
                $upper = strtolower($real_section_name[0]) !== $real_section_name[0];
            } else if ($real_base_controller = Util::guessPath(app_path(), 'controller')) {
                $upper = strtolower($real_base_controller[0]) !== $real_base_controller[0];
            }
            $upper = $upper ?? strtolower($name_str[0]) !== $name_str[0];
            if ($upper && !$real_name_str) {
                $name_str = preg_replace_callback('/\/([a-z])/', function ($matches) {
                    return '/' . strtoupper($matches[1]);
                }, ucfirst($name_str));
            }
            $path = "$name_str/" . ($upper ? 'Model' : 'model');
            $name = ucfirst(substr($name, $pos + 1));
            $file = app_path() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "$name.php";
            $namespace = str_replace('/', '\\', ($upper ? 'App/' : 'app/') . $path);
        }
        if (!$type) {
            $database = config('database');
            if (isset($database['default']) && strpos($database['default'], 'plugin.') === 0) {
                $database = false;
            }
            $thinkorm = config('think-orm') ?: config('thinkorm');
            if (isset($thinkorm['default']) && strpos($thinkorm['default'], 'plugin.') === 0) {
                $thinkorm = false;
            }
            $type = !$database && $thinkorm ? 'tp' : 'laravel';
        }

        if (is_file($file)) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        if ($type == 'tp') {
            $this->createTpModel($name, $namespace, $file, $connection);
        } else {
            $this->createModel($name, $namespace, $file, $connection);
        }

        return self::SUCCESS;
    }

    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @param string|null $connection
     * @return void
     */
    protected function createModel($class, $namespace, $file, $connection = null)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $table_val = 'null';
        $pk = 'id';
        $properties = '';
        $connection = $connection ?: config('database.default');
        $timestamps = 'false';
        $hasCreatedAt = false;
        $hasUpdatedAt = false;
        try {
            $prefix = config("database.connections.$connection.prefix") ?? '';
            $database = config("database.connections.$connection.database");
            $driver = config("database.connections.$connection.driver") ?? 'mysql';
            $inflector = InflectorFactory::create()->build();
            $table_plura = $inflector->pluralize($inflector->tableize($class));
            $con = Db::connection($connection);
            
            // 检查表是否存在（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表检查
                $schema = config("database.connections.$connection.schema") ?? 'public';
                $exists_plura = $con->select("SELECT to_regclass('{$schema}.{$prefix}{$table_plura}') as table_exists");
                $exists = $con->select("SELECT to_regclass('{$schema}.{$prefix}{$table}') as table_exists");
                
                if (!empty($exists_plura[0]->table_exists)) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table_plura}";
                } else if (!empty($exists[0]->table_exists)) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table}";
                }
            } else {
                // MySQL 表检查
                if ($con->select("show tables like '{$prefix}{$table_plura}'")) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table_plura}";
                } else if ($con->select("show tables like '{$prefix}{$table}'")) {
                    $table_val = "'$table'";
                    $table = "{$prefix}{$table}";
                }
            }

            // 获取表注释和列信息（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表注释
                $schema = config("database.connections.$connection.schema") ?? 'public';
                $tableComment = $con->select("SELECT obj_description('{$schema}.{$table}'::regclass) as table_comment");
                if (!empty($tableComment) && !empty($tableComment[0]->table_comment)) {
                    $comments = $tableComment[0]->table_comment;
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }
                
                // PostgreSQL 列信息
                $columns = $con->select("
                    SELECT 
                        a.attname as column_name,
                        format_type(a.atttypid, a.atttypmod) as data_type,
                        CASE WHEN con.contype = 'p' THEN 'PRI' ELSE '' END as column_key,
                        d.description as column_comment
                    FROM pg_catalog.pg_attribute a
                    LEFT JOIN pg_catalog.pg_description d ON d.objoid = a.attrelid AND d.objsubid = a.attnum
                    LEFT JOIN pg_catalog.pg_constraint con ON con.conrelid = a.attrelid AND a.attnum = ANY(con.conkey) AND con.contype = 'p'
                    WHERE a.attrelid = '{$schema}.{$table}'::regclass
                    AND a.attnum > 0 AND NOT a.attisdropped
                    ORDER BY a.attnum
                ");
                
                foreach ($columns as $item) {
                    if ($item->column_key === 'PRI') {
                        $pk = $item->column_name;
                        $item->column_comment = ($item->column_comment ? $item->column_comment . ' ' : '') . "(主键)";
                    }
                    $type = $this->getType($item->data_type);
                    if ($item->column_name === 'created_at') {
                        $hasCreatedAt = true;
                    }
                    if ($item->column_name === 'updated_at') {
                        $hasUpdatedAt = true;
                    }
                    $properties .= " * @property $type \${$item->column_name} " . ($item->column_comment ?? '') . "\n";
                }
                
            } else {
                // MySQL 表注释
                $tableComment = $con->select('SELECT table_comment FROM information_schema.`TABLES` WHERE table_schema = ? AND table_name = ?', [$database, $table]);
                if (!empty($tableComment)) {
                    $comments = $tableComment[0]->table_comment ?? $tableComment[0]->TABLE_COMMENT;
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }
                
                // MySQL 列信息
                foreach ($con->select("select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '$table' and table_schema = '$database' ORDER BY ordinal_position") as $item) {
                    if ($item->COLUMN_KEY === 'PRI') {
                        $pk = $item->COLUMN_NAME;
                        $item->COLUMN_COMMENT .= "(主键)";
                    }
                    $type = $this->getType($item->DATA_TYPE);
                    if ($item->COLUMN_NAME === 'created_at') {
                        $hasCreatedAt = true;
                    }
                    if ($item->COLUMN_NAME === 'updated_at') {
                        $hasUpdatedAt = true;
                    }
                    $properties .= " * @property $type \${$item->COLUMN_NAME} {$item->COLUMN_COMMENT}\n";
                }
            }
        } catch (\Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        $properties = rtrim($properties) ?: ' *';
        $timestamps = $hasCreatedAt && $hasUpdatedAt ? 'true' : 'false';
        $model_content = <<<EOF
<?php

namespace $namespace;

use support\Model;

/**
$properties
 */
class $class extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected \$connection = '$connection';
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$primaryKey = '$pk';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public \$timestamps = $timestamps;
    
    
}

EOF;
        file_put_contents($file, $model_content);
    }


    /**
     * @param $class
     * @param $namespace
     * @param $file
     * @param string|null $connection
     * @return void
     */
    protected function createTpModel($class, $namespace, $file, $connection = null)
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $table = Util::classToName($class);
        $is_thinkorm_v2 = class_exists(\support\think\Db::class);
        $table_val = 'null';
        $pk = 'id';
        $properties = '';
        $connection = $connection ?: 'mysql';
        try {
            $config_name = $is_thinkorm_v2 ? 'think-orm' : 'thinkorm';
            $prefix = config("$config_name.connections.$connection.prefix") ?? '';
            $database = config("$config_name.connections.$connection.database");
            $driver = config("$config_name.connections.$connection.type") ?? 'mysql';
            
            if ($is_thinkorm_v2) {
                $con = \support\think\Db::connect($connection);
            } else {
                $con = \think\facade\Db::connect($connection);
            }

            // 检查表是否存在（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表检查
                $schema = config("$config_name.connections.$connection.schema") ?? 'public';
                $exists = $con->query("SELECT to_regclass('{$schema}.{$prefix}{$table}') as table_exists");
                $exists_plural = $con->query("SELECT to_regclass('{$schema}.{$prefix}{$table}s') as table_exists");
                
                if (!empty($exists[0]['table_exists'])) {
                    $table = "{$prefix}{$table}";
                    $table_val = "'$table'";
                } else if (!empty($exists_plural[0]['table_exists'])) {
                    $table = "{$prefix}{$table}s";
                    $table_val = "'$table'";
                }
            } else {
                // MySQL 表检查
                if ($con->query("show tables like '{$prefix}{$table}'")) {
                    $table = "{$prefix}{$table}";
                    $table_val = "'$table'";
                } else if ($con->query("show tables like '{$prefix}{$table}s'")) {
                    $table = "{$prefix}{$table}s";
                    $table_val = "'$table'";
                }
            }

            // 获取表注释和列信息（兼容MySQL和PostgreSQL）
            if ($driver === 'pgsql') {
                // PostgreSQL 表注释
                $schema = config("$config_name.connections.$connection.schema") ?? 'public';
                $tableComment = $con->query("SELECT obj_description('{$schema}.{$table}'::regclass) as table_comment");
                if (!empty($tableComment) && !empty($tableComment[0]['table_comment'])) {
                    $comments = $tableComment[0]['table_comment'];
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }
                
                // PostgreSQL 列信息
                $columns = $con->query("
                    SELECT 
                        a.attname as column_name,
                        format_type(a.atttypid, a.atttypmod) as data_type,
                        CASE WHEN con.contype = 'p' THEN 'PRI' ELSE '' END as column_key,
                        d.description as column_comment
                    FROM pg_catalog.pg_attribute a
                    LEFT JOIN pg_catalog.pg_description d ON d.objoid = a.attrelid AND d.objsubid = a.attnum
                    LEFT JOIN pg_catalog.pg_constraint con ON con.conrelid = a.attrelid AND a.attnum = ANY(con.conkey) AND con.contype = 'p'
                    WHERE a.attrelid = '{$schema}.{$table}'::regclass
                    AND a.attnum > 0 AND NOT a.attisdropped
                    ORDER BY a.attnum
                ");
                
                foreach ($columns as $item) {
                    if ($item['column_key'] === 'PRI') {
                        $pk = $item['column_name'];
                        $item['column_comment'] = ($item['column_comment'] ? $item['column_comment'] . ' ' : '') . "(主键)";
                    }
                    $type = $this->getType($item['data_type']);
                    $properties .= " * @property $type \${$item['column_name']} " . ($item['column_comment'] ?? '') . "\n";
                }
            } else {
                // MySQL 表注释
                $tableComment = $con->query('SELECT table_comment FROM information_schema.`TABLES` WHERE table_schema = ? AND table_name = ?', [$database, $table]);
                if (!empty($tableComment)) {
                    $comments = $tableComment[0]['table_comment'] ?? $tableComment[0]['TABLE_COMMENT'];
                    $properties .= " * {$table} {$comments}" . PHP_EOL;
                }
                
                // MySQL 列信息
                foreach ($con->query("select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '$table' and table_schema = '$database' ORDER BY ordinal_position") as $item) {
                    if ($item['COLUMN_KEY'] === 'PRI') {
                        $pk = $item['COLUMN_NAME'];
                        $item['COLUMN_COMMENT'] .= "(主键)";
                    }
                    $type = $this->getType($item['DATA_TYPE']);
                    $properties .= " * @property $type \${$item['COLUMN_NAME']} {$item['COLUMN_COMMENT']}\n";
                }
            }
        } catch (\Throwable $e) {
            echo $e;
        }
        $properties = rtrim($properties) ?: ' *';
        $modelNamespace = $is_thinkorm_v2 ? 'support\think\Model' : 'think\Model';
        $model_content = <<<EOF
<?php

namespace $namespace;

use $modelNamespace;

/**
$properties
 */
class $class extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected \$connection = '$connection';
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = $table_val;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected \$pk = '$pk';

    
}

EOF;
        file_put_contents($file, $model_content);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getType(string $type)
    {
        if (strpos($type, 'int') !== false) {
            return 'integer';
        }
        
        if (strpos($type, 'character varying') !== false || strpos($type, 'varchar') !== false) {
            return 'string';
        }
        
        if (strpos($type, 'timestamp') !== false) {
            return 'string';
        }
        
        switch ($type) {
            case 'varchar':
            case 'string':
            case 'text':
            case 'date':
            case 'time':
            case 'guid':
            case 'datetimetz':
            case 'datetime':
            case 'decimal':
            case 'enum':
            case 'character':   // PostgreSQL类型
            case 'char':        // PostgreSQL类型
            case 'json':        // PostgreSQL类型
            case 'jsonb':       // PostgreSQL类型
            case 'uuid':        // PostgreSQL类型
            case 'timestamptz': // PostgreSQL类型
            case 'citext':      // PostgreSQL类型
                return 'string';
            case 'boolean':
            case 'bool':        // PostgreSQL类型
                return 'integer';
            case 'float':
            case 'float4':      // PostgreSQL类型 (real)
            case 'float8':      // PostgreSQL类型 (double precision)
                return 'float';
            case 'numeric':     // PostgreSQL类型
                return 'string';
            default:
                return 'mixed';
        }
    }

}
