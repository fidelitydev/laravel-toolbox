<?php

namespace Knighttower\Toolbox\Utilities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


// Helpers
use Knighttower\Toolbox\Helpers\DBHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Filters
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Parent model using the trait or this class
     *
     * @var object
     */
    protected $model;

    /**
     * Passed directly via params, if any
     *
     * @var array|object
     */
    protected $filters;

    /**
     * Set at the Custom Filter level
     *
     * @var null|object
     */
    protected $filterClass;

    /**
     * If the Filter class exists, collect its methods
     *
     * @var array
     */
    protected array $filterClassMethods;

    /**
     * Filters constructor.
     *
     * @param Builder $builder
     * @param array|object $filters
     * @param null|string $filterClass
     * @param object $model
     * @return void;
     * @throws \Exception
     * @usage Filters::__construct($builder, $filters, $filterClass, $model);
     *
     */
    public function __construct(Builder $builder, $filters, ?string $filterClass, $model)
    {
        // Set Vars
        $this->builder = $builder;
        $this->filters = $filters;
        $this->request = request();
        $this->model = $model;

        if (!empty($filters) && !(Arr::isAssoc($filters) || is_object($filters))) {
            abort(500, 'Filters must be an associative array or object');
        }

        if ($filterClass && class_exists($filterClass)) {
            $this->filterClass = new $filterClass($this->builder);
            $this->filterClassMethods = get_class_methods($this->filterClass);
        }
    }


    /**
     * Apply filters
     *
     * @return Builder
     */
    public function apply(): Builder
    {
        $filters = $this->getFilters();
        $columns = empty($this->filterClass) ? $this->getTableColumns() : [];

        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                if (!empty($this->filterClass)) {
                    if (!empty($value)) {
                        $this->filterClass->$filter($value);
                    } else {
                        $this->filterClass->$filter();
                    }
                } else {
                    if (in_array($filter, $columns)) {
                        $this->builder->where(Str::snake($filter), 'like', '%' . $value . '%');
                    }
                }
            }
        }

        return $this->builder;
    }


    /**
     * Fetch all relevant filters from the request.
     *
     * @internal
     * @return array
     */
    private function getFilters(): ?array
    {
        // If direct filters are not set, grab them from the request
        if (empty($this->filters)) {
            // Try to collect from different locations by priority
            $queryString = $this->request->query();
            $params = $this->request->all();

            if (!empty($queryString)) {
                $this->filters = array_filter($queryString);
            } elseif (!empty($params)) {
                $this->filters = array_filter($params);
            }
        }

        // Convert any filters into an array
        if (!empty($this->filters)) {
            $this->filters = DBHelper::keysToUserFormat($this->filters);
            $this->filters = collect($this->filters);
            // If a filter class is used, use only those matching its methods
            if (!empty($this->filterClass)) {
                $this->filters = $this->filters->only($this->filterClassMethods);
            } else {
                $this->filters = $this->filters
                    ->only($this->getTableColumns());
            }

            $this->filters = $this->filters->all();
        }

        return DBHelper::keysToUserFormat($this->filters);
    }


    /**
     * Fetch all columns from the table.
     *
     * @internal
     * @return array
     */
    private function getTableColumns(): array
    {
        $columns = DBHelper::getTableColumns($this->model->getTable());
        return DBHelper::keysToUserFormat(
            collect($columns)
                ->flatten()
                ->unique()
                ->all()
        );
    }
}