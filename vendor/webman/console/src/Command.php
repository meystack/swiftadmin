<?php

namespace Webman\Console;

use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as Commands;
use support\Container;

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
            // abc\def.php
            $relativePath = str_replace(str_replace('/', '\\', $path . '\\'), '', str_replace('/', '\\', $file->getRealPath()));
            // app\command\abc
            $realNamespace = trim($namspace . '\\' . trim(dirname(str_replace('\\', DIRECTORY_SEPARATOR, $relativePath)), '.'), '\\');
            $realNamespace =  str_replace('/', '\\', $realNamespace);
            // app\command\doc\def
            $class_name = trim($realNamespace . '\\' . $file->getBasename('.php'), '\\');
            if (!class_exists($class_name) || !is_a($class_name, Commands::class, true)) {
                continue;
            }
            $reflection = new \ReflectionClass($class_name);
            if ($reflection->isAbstract()) {
                continue;
            }
            $properties = $reflection->getStaticProperties();
            $name = $properties['defaultName'] ?? null;
            if (!$name) {
                throw new RuntimeException("Command {$class_name} has no defaultName");
            }
            $description = $properties['defaultDescription'] ?? '';
            $command = Container::get($class_name);
            $command->setName($name)->setDescription($description);
            $this->add($command);
        }
    }
}
