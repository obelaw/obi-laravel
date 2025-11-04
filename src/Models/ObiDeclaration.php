<?php

namespace Obelaw\Obi\Models;

use Illuminate\Database\Eloquent\Model;
use Obelaw\Obi\Declaration;

class ObiDeclaration extends Model
{
    protected $table = 'obi_declarations';

    protected $fillable = [
        'file',
        'function_name',
        'function_description',
        'declaration',
        'target_class',
        'target_method',
        'tag',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get the unserialized declaration instance.
     *
     * @return Declaration|null
     */
    public function getDeclarationInstance(): ?Declaration
    {
        if (!$this->declaration) {
            return null;
        }

        try {
            return unserialize($this->declaration);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Set the declaration instance (serialize it).
     *
     * @param Declaration $declaration
     * @return void
     */
    public function setDeclarationInstance(Declaration $declaration): void
    {
        $this->declaration = serialize($declaration);
    }

    /**
     * Scope to get only enabled declarations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope to filter by tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tag
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->where('tag', $tag);
    }
}