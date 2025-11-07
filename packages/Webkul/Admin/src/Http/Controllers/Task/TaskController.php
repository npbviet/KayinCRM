<?php

namespace Webkul\Admin\Http\Controllers\Task;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Task\TaskDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\AttributeForm;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Task\Repositories\TaskRepository;
use Webkul\User\Repositories\UserRepository;

class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected TaskRepository $taskRepository,
        protected UserRepository $userRepository,
        protected AttributeRepository $attributeRepository
    ) {
        request()->request->add(['entity_type' => 'tasks']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(TaskDataGrid::class)->process();
        }

        return view('admin::tasks.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::tasks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request): RedirectResponse|JsonResponse
    {
        Event::dispatch('task.create.before');

        $data = request()->all();

        $data['status'] = $data['status'] ?? 'pending';
        $data['user_id'] = $data['user_id'] ?? auth()->user()->id;

        $task = $this->taskRepository->create($data);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.tasks.index.create-success'),
                'data'    => $task,
            ]);
        }

        Event::dispatch('task.create.after', $task);

        session()->flash('success', trans('admin::app.tasks.index.create-success'));

        return redirect()->route('admin.tasks.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $task = $this->taskRepository->findOrFail($id);

        return view('admin::tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse|JsonResponse
    {
        Event::dispatch('task.update.before', $id);

        $task = $this->taskRepository->update(request()->all(), $id);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('admin::app.tasks.index.update-success'),
                'data'    => $task,
            ]);
        }

        Event::dispatch('task.update.after', $task);

        session()->flash('success', trans('admin::app.tasks.index.update-success'));

        return redirect()->route('admin.tasks.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->taskRepository->findOrFail($id);

        try {
            Event::dispatch('task.delete.before', $id);

            $this->taskRepository->delete($id);

            Event::dispatch('task.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.tasks.index.destroy-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.tasks.index.destroy-failed'),
            ], 400);
        }
    }

    /**
     * Mass destroy the resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('task.delete.before', $index);

            $this->taskRepository->delete($index);

            Event::dispatch('task.delete.after', $index);
        }

        return response()->json([
            'message' => trans('admin::app.tasks.index.mass-destroy-success'),
        ]);
    }
}
