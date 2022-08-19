<?php

namespace Webman\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as Commands;

class Command extends Application
{
    public function installInternalCommands()
    {
        $this->installCommands(__DIR__ . '/Commands', 'Webman\Console\Commands');
    }

    public function installCommands($path, $namspace = 'app\command')
    {
        $dir_iterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $class_name = $namspace.'\\'.basename($file, '.php');
            if (!is_a($class_name, Commands::class, true)) {
                continue;
            }
            $this->add(new $class_name);
        }
    }
}
