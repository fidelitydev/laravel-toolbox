<?php

namespace Knighttower\Toolbox\Traits\Model;

use Illuminate\Database\Eloquent\Builder;
use Knighttower\Toolbox\Utilities\Filters;

trait HasFilters
{
    //============================================
    //-----------------------------------
    //------ SCOPES
    //-------------------------------

    /*
    * @Step 1: Add the filter class to the model
        class ClassName extends Model
        {
            use HasFilters;

            protected string $filterClass = FilterClassName::class;

            // Model Code...
        }
    */

    /*
    * @Step 2: create the filter class
        namespace App\Filters;
        use Knighttower\Toolbox\Abstract\Filters;
        class ClassName extends Filters
        {
            // Filter Code...

            public function name($name)
            {
                return $this->builder->where('name', 'like', '%' . $name . '%');
            }
        }
    */

    /**
     * Scope a query to only include specific criteria.
     *
     * @param Builder $builder
     * @param instace|array|object $filters Manually pass The filters to apply to the query
     * @return Builder The Filters class will return the builder instance
     * @usage Include this trait in your model and use the filter method to apply filters to your query
     * @example User::filter(['name' => 'John'])->paginate(15) // manually pass filters
     * @example User::filter()->paginate(15) // will use request()->all() as filters
     */
    public function scopeFilter(Builder $builder, $filters = null): Builder
    {
        $filterClass = emptyOrValue($this->filterClass ?? null);
        $filters = new Filters($builder, $filters, $filterClass, $this);

        return $filters->apply();
    }
}
