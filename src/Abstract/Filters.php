<?php

namespace Knighttower\Toolbox\Abstract;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

/**
* Abstrac class as base for common code in Filter Classes
 *
* @see \Knighttower\Toolbox\Traits\Model\HasFilters Insert in Model if Filters are needed
* @see \Knighttower\Toolbox\Utilities\Filters To see related functionality
* @example In Model Class add use HasFilters, and add the name of the Filter class $filterClass
*/
/*
* @example:
    class ClassName extends Model
    {
        use HasFilters;

        protected string $filterClass = FilterClassName::class;

        // Model Code...
    }
 */
abstract class Filters
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * Create a Filters instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }
}
