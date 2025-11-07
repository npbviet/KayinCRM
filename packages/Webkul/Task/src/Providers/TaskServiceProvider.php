<?php

namespace Webkul\Task\Providers;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'tasks');

        Breadcrumbs::for('tasks', function ($trail) {
            $trail->push(trans('admin::app.tasks.index.title') ?? 'Tasks', route('admin.tasks.index'));
        });

        Breadcrumbs::for('tasks.create', function ($trail) {
            $trail->parent('tasks');
            $trail->push(trans('admin::app.tasks.create.title') ?? 'Create Task');
        });

        Breadcrumbs::for('tasks.edit', function ($trail, $task) {
            $trail->parent('tasks');
            $trail->push(trans('admin::app.tasks.edit.title') ?? 'Edit Task');
        });
    }
}
