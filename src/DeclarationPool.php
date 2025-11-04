<?php

namespace Obelaw\Obi;

class DeclarationPool
{
    /**
     * These paths for all pools of declarations.
     *
     * @var array
     */
    protected static array $paths = [];

    /**
     * Add a path to the paths array.
     *
     * @param string $path
     */
    public static function addPath(string $path): void
    {
        if (!in_array($path, self::$paths)) {
            self::$paths[] = $path;
        }
    }

    /**
     * Get the paths array.
     *
     * @return array
     */
    public static function getPaths(): array
    {
        return self::$paths;
    }

    /**
     * Get all declaration instances from the paths.
     *
     * @return array
     */
    public static function getDeclarations(): array
    {
        $declarations = [];

        foreach (self::$paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path . '/*.php');
            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                try {
                    $declarationInstance = require $file;
                    
                    if ($declarationInstance instanceof Declaration) {
                        $declarations[] = [
                            'instance' => $declarationInstance,
                            'file' => basename($file),
                            'path' => $path,
                        ];
                    }
                } catch (\Throwable $e) {
                    // Skip invalid declaration files
                    continue;
                }
            }
        }

        return $declarations;
    }

    /**
     * Get all function declarations for Gemini.
     *
     * @return array
     */
    public static function getFunctionDeclarations(): array
    {
        $declarations = self::getDeclarations();
        $functionDeclarations = [];

        foreach ($declarations as $item) {
            try {
                $functionDeclarations[] = $item['instance']->declaration();
            } catch (\Throwable $e) {
                // Skip if declaration() fails
                continue;
            }
        }

        return $functionDeclarations;
    }
}