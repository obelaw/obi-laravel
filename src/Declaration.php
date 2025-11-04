<?php

namespace Obelaw\Obi;

use Gemini\Data\FunctionDeclaration;

abstract class Declaration
{
    /**
     * Get the function declaration for Gemini.
     *
     * @return FunctionDeclaration
     */
    abstract public function declaration(): FunctionDeclaration;

    /**
     * Get the target class to execute.
     *
     * @return string
     */
    abstract public function targetClass(): string;

    /**
     * Get the target method to execute.
     *
     * @return string
     */
    abstract public function targetMethod(): string;

    /**
     * Get the name of the declaration.
     * Defaults to the function name from declaration().
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->declaration()->name;
    }

    /**
     * Get the description of the declaration.
     * Defaults to the function description from declaration().
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->declaration()->description ?? '';
    }

    /**
     * Get the tag for grouping declarations.
     *
     * @return string|null
     */
    public function getTag(): ?string
    {
        return null;
    }

    /**
     * Get the priority for ordering declarations.
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * Execute the declaration logic.
     * Override this method in your declaration to handle the function call.
     *
     * @param array $args
     * @return mixed
     */
    public function execute(array $args = [])
    {
        $class = $this->targetClass();
        $method = $this->targetMethod();

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