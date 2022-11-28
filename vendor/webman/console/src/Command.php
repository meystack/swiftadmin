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
            /** @var \SplFileInfo $file */
            if (strpos($file->getFilename(), '.') === 0) {
                continue;
            }
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $relativePath = str_replace(str_replace('/', '\\', $path . '\\'), '', str_replace('/', '\\', $file->getRealPath()));
            $realNamespace = trim($namspace . '\\' . trim(dirname($relativePath), '.'), '\\');
            $class_name = trim($realNamespace . '\\' . $file->getBasename('.php'), '\\');
            if (!is_a($class_name, Commands::class, true)) {
                continue;
            }
            $this->add(new $class_name);
        }
    }
}
