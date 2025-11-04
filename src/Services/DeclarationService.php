<?php

namespace Obelaw\Obi\Services;

use Obelaw\Obi\Models\ObiDeclaration;

class DeclarationService
{
    /**
     * Get all declarations from database as instances.
     *
     * @param bool $enabledOnly
     * @return array
     */
    public function getAll(bool $enabledOnly = true): array
    {
        $query = ObiDeclaration::query();

        if ($enabledOnly) {
            $query->enabled();
        }

        $declarations = $query->get();
        $instances = [];

        foreach ($declarations as $declaration) {
            $instance = unserialize($declaration->declaration);
            if ($instance) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    /**
     * Get target class and method by function name.
     *
     * @param string $functionName
     * @return array|null
     */
    public function getByFunctionName(string $functionName): ?array
    {
        $declaration = ObiDeclaration::where('function_name', $functionName)
            ->enabled()
            ->first();

        if (!$declaration) {
            return null;
        }

        return [
            'target_class' => $declaration->target_class,
            'target_method' => $declaration->target_method,
        ];
    }

    /**
     * Execute a declaration by function name.
     *
     * @param string $functionName
     * @param array $args
     * @return mixed
     */
    public function execute(string $functionName, array $args = [])
    {
        $target = $this->getByFunctionName($functionName);

        if (!$target) {
            throw new \Exception("Declaration not found: {$functionName}");
        }

        $class = $target['target_class'];
        $method = $target['target_method'];

        if (!$class || !$method) {
            throw new \Exception("Target class or method not set for: {$functionName}");
        }

        if (!class_exists($class)) {
            throw new \Exception("Target class not found: {$class}");
        }

        $instance = app($class);

        if (!method_exists($instance, $method)) {
            throw new \Exception("Target method not found: {$class}::{$method}");
        }

        return $instance->{$method}($args);
    }
}
