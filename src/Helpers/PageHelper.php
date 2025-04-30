<?php

namespace Knighttower\Toolbox\Helpers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Knighttower\Toolbox\Helpers\DBHelper;

class PageHelper
{
    /**
     * @var object
     */
    private object $model;

    /**
     * @var array|null
     */
    private ?array $filters;


    /**
     * Create a new PageHelper instance.
     *
     * @param Collection|LengthAwarePaginator $model
     * @return void
     */
    public function __construct(object $model)
    {
        $this->model = $model;
        $this->filters = null;
    }

    /**
     * Set the filters
     *
     * @param array $filters
     * @return self
     */
    public function withFilters(array $filters = []): PageHelper
    {
        $filters = emptyOrValue($filters, request()->getQueryString());
        if (is_string($filters)) {
            parse_str($filters, $filters);
        }
        $this->filters = $filters;

        if (method_exists($this->model, 'appends')) {
            $this->model->appends($filters);
        }

        return $this;
    }

    /**
     * Paginate the given query.
     *
     * @param int $pageSize
     * @return object With via ->get LengthAwarePaginator and Collection via ->collection.
     * @throws BindingResolutionException Error.
     */
    public function paginate(int $pageSize = 15): object
    {
        if ($this->model instanceof LengthAwarePaginator) {
            return $this->buildPaginatorMeta($this->model);
        }

        $page = Paginator::resolveCurrentPage('page');

        $total = $this->model->count();

        return $this->buildPaginatorMeta($this->paginator($this->model->forPage($page, $pageSize), $total, $pageSize, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]));
    }

    /**
     * Create a new length-aware paginator instance.
     *
     * @param Collection $items
     * @param int $total
     * @param int $perPage
     * @param int $currentPage
     * @param array $options
     * @return LengthAwarePaginator
     *
     * @throws BindingResolutionException If can't make LengthAwarePaginator instance.
     */
    private function paginator(
        Collection $items,
        int $total,
        int $perPage,
        int $currentPage,
        array $options
    ): LengthAwarePaginator { // phpcs:ignore
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items',
            'total',
            'perPage',
            'currentPage',
            'options'
        ));
    }

    /**
     * Get the Meta
     *
     * @param object $model
     * @return object
     */
    private function buildPaginatorMeta(object $model): object
    {
        $metaInfo = DBHelper::keysToUserFormat((collect($model)->except([
            'data'
        ]))->all());
        $metaInfo['resultsPerPage'] = $metaInfo['perPage'];
        $metaInfo['currentTotal'] = $model->count();
        $metaInfo['totalResults'] = $model->total() ?? count($model->items());
        $metaInfo['filters'] = $this->filters;
        $metaInfo['get'] = $model;
        $metaInfo['collection'] = collect($metaInfo);

        return (object) $metaInfo;
    }
}
