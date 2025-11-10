<?php

namespace Webkul\Admin\DataGrids\Task;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TaskDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('tasks')
            ->addSelect(
                'tasks.id',
                'tasks.title',
                'tasks.status',
                'tasks.priority',
                'tasks.due_date',
                'tasks.created_at',
                'users.id as user_id',
                'users.name as owner_name',
                'assigned_users.id as assigned_user_id',
                'assigned_users.name as assigned_user_name'
            )
            ->leftJoin('users', 'tasks.user_id', '=', 'users.id')
            ->leftJoin('users as assigned_users', 'tasks.assigned_to', '=', 'assigned_users.id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('tasks.user_id', $userIds);
        }

        $this->addFilter('id', 'tasks.id');
        $this->addFilter('user', 'tasks.user_id');
        $this->addFilter('status', 'tasks.status');
        $this->addFilter('priority', 'tasks.priority');
        $this->addFilter('due_date', 'tasks.due_date');
        $this->addFilter('created_at', 'tasks.created_at');
        $this->addFilter('owner_name', 'users.name');
        $this->addFilter('assigned_user_name', 'assigned_users.name');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('admin::app.tasks.index.datagrid.title'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.tasks.index.datagrid.status'),
            'type'               => 'string',
            'sortable'           => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.tasks.status.pending'),
                    'value' => 'pending',
                ],
                [
                    'label' => trans('admin::app.tasks.status.in_progress'),
                    'value' => 'in_progress',
                ],
                [
                    'label' => trans('admin::app.tasks.status.completed'),
                    'value' => 'completed',
                ],
                [
                    'label' => trans('admin::app.tasks.status.cancelled'),
                    'value' => 'cancelled',
                ],
            ],
        ]);

        $this->addColumn([
            'index'              => 'priority',
            'label'              => trans('admin::app.tasks.index.datagrid.priority'),
            'type'               => 'string',
            'sortable'           => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.tasks.priority.none'),
                    'value' => '',
                ],
                [
                    'label' => trans('admin::app.tasks.priority.low'),
                    'value' => 'low',
                ],
                [
                    'label' => trans('admin::app.tasks.priority.medium'),
                    'value' => 'medium',
                ],
                [
                    'label' => trans('admin::app.tasks.priority.high'),
                    'value' => 'high',
                ],
                [
                    'label' => trans('admin::app.tasks.priority.urgent'),
                    'value' => 'urgent',
                ],
            ],
        ]);

        $this->addColumn([
            'index'              => 'owner_name',
            'label'              => trans('admin::app.tasks.index.datagrid.owner'),
            'type'               => 'string',
            'sortable'           => true,
            'filterable'         => true,
            'filterable_type'    => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => \Webkul\User\Repositories\UserRepository::class,
                'column'     => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index'      => 'due_date',
            'label'      => trans('admin::app.tasks.index.datagrid.due-date'),
            'type'       => 'date',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => fn ($row) => $row->due_date ? core()->formatDate($row->due_date) : '-',
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.tasks.index.datagrid.created-at'),
            'type'       => 'date',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
            'closure'    => fn ($row) => core()->formatDate($row->created_at),
        ]);

        $this->addColumn([
            'index'              => 'assigned_user_name',
            'label'              => trans('admin::app.tasks.index.datagrid.assigned-to'),
            'type'               => 'string',
            'sortable'           => true,
            'filterable'         => true,
            'filterable_type'    => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => \Webkul\User\Repositories\UserRepository::class,
                'column'     => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('tasks.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.tasks.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.tasks.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('tasks.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('admin::app.tasks.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('admin.tasks.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon'   => 'icon-delete',
            'title'  => trans('admin::app.tasks.index.datagrid.delete'),
            'method' => 'POST',
            'url'    => route('admin.tasks.mass_delete'),
        ]);
    }

    /**
     * Process all requested filters.
     * Override to customize search behavior for Task.
     * When searching with 'all', only search in: title, owner_name, assigned_user_name
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function processRequestedFilters(array $requestedFilters)
    {
        // Hợp nhất filters nằm trong 'columns' (nếu có)
        $columnsFilters = $requestedFilters['columns'] ?? [];
        $filters = array_merge($requestedFilters, $columnsFilters);

        foreach ($filters as $requestedColumn => $requestedValues) {
            // Skip nếu là 'columns' (tránh lặp)
            if ($requestedColumn === 'columns') {
                continue;
            }

            // --- Custom search for "all" ---
            if ($requestedColumn === 'all') {
                $this->queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
                    foreach ((array) $requestedValues as $value) {
                        $scopeQueryBuilder->where(function ($innerQueryBuilder) use ($value) {
                            $innerQueryBuilder
                                ->orWhere('tasks.title', 'LIKE', "%{$value}%")
                                ->orWhere('tasks.description', 'LIKE', "%{$value}%")
                                ->orWhere('users.name', 'LIKE', "%{$value}%")
                                ->orWhere('assigned_users.name', 'LIKE', "%{$value}%");
                        });
                    }
                });

                continue;
            }

            $column = collect($this->columns)
                ->first(fn ($col) => $col->getIndex() === $requestedColumn);

            if (! $column) {
                continue;
            }

            // --- Handle DATE (single or range) ---
            if ($column->getType() === 'date') {
                if (is_string($requestedValues)) {
                    $this->queryBuilder->whereDate($column->getColumnName(), '=', $requestedValues);

                    continue;
                }

                if (is_array($requestedValues)) {
                    $from = $requestedValues['from'] ?? null;
                    $to = $requestedValues['to'] ?? null;

                    $this->queryBuilder->where(function ($query) use ($column, $from, $to) {
                        if ($from && $to) {
                            $query->whereBetween($column->getColumnName(), [$from, $to]);
                        } elseif ($from) {
                            $query->whereDate($column->getColumnName(), '>=', $from);
                        } elseif ($to) {
                            $query->whereDate($column->getColumnName(), '<=', $to);
                        }
                    });

                    continue;
                }
            }

            // --- Default filter processing ---
            $column->processFilter($this->queryBuilder, (array) $requestedValues);
        }

        return $this->queryBuilder;
    }
}
