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
            'index'      => 'status',
            'label'      => trans('admin::app.tasks.index.datagrid.status'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'priority',
            'label'      => trans('admin::app.tasks.index.datagrid.priority'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'owner_name',
            'label'      => trans('admin::app.tasks.index.datagrid.owner'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'assigned_user_name',
            'label'      => trans('admin::app.tasks.index.datagrid.assigned-to'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
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
        foreach ($requestedFilters as $requestedColumn => $requestedValues) {
            if ($requestedColumn === 'all') {
                // Custom search: chỉ search trong các trường cụ thể
                $this->queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
                    foreach ($requestedValues as $value) {
                        $scopeQueryBuilder->where(function ($innerQueryBuilder) use ($value) {
                            // Search trong title
                            $innerQueryBuilder->orWhere('tasks.title', 'LIKE', '%'.$value.'%');

                            // Search trong description
                            $innerQueryBuilder->orWhere('tasks.description', 'LIKE', '%'.$value.'%');
                            
                            // Search trong owner name
                            $innerQueryBuilder->orWhere('users.name', 'LIKE', '%'.$value.'%');

                            // Search trong assigned user name
                            $innerQueryBuilder->orWhere('assigned_users.name', 'LIKE', '%'.$value.'%');

                        });
                    }
                });
            } else {
                // Xử lý filter cho các column cụ thể (giữ nguyên logic mặc định)
                collect($this->columns)
                    ->first(fn ($column) => $column->getIndex() === $requestedColumn)
                    ->processFilter($this->queryBuilder, $requestedValues);
            }
        }

        return $this->queryBuilder;
    }
}
