<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.tasks.edit.title')
        </x-slot>

        <x-admin::form
            :action="route('admin.tasks.update', $task->id)"
            method="PUT">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div class="flex flex-col gap-2">
                        <x-admin::breadcrumbs
                            name="tasks.edit" />

                        <div class="text-xl font-bold dark:text-white">
                            @lang('admin::app.tasks.edit.title')
                        </div>
                    </div>

                    <div class="flex items-center gap-x-2.5">
                        <button
                            type="submit"
                            class="primary-button">
                            @lang('admin::app.tasks.edit.save-btn')
                        </button>
                    </div>
                </div>

                <div class="box-shadow flex flex-col gap-4 rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="p-4">
                        <div class="grid grid-cols-1 gap-4">
                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.edit.title')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="title"
                                    :value="old('title', $task->title)"
                                    rules="required"
                                    :label="trans('admin::app.tasks.edit.title')" />

                                <x-admin::form.control-group.error
                                    control-name="title" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.edit.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="description"
                                    :value="old('description', $task->description)"
                                    :label="trans('admin::app.tasks.edit.description')" />

                                <x-admin::form.control-group.error
                                    control-name="description" />
                            </x-admin::form.control-group>

                            <!-- Status -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="status"
                                    :value="old('status', $task->status)"
                                    rules="required"
                                    :label="trans('admin::app.tasks.edit.status')">
                                    <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>@lang('admin::app.tasks.status.pending')</option>
                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>@lang('admin::app.tasks.status.in_progress')</option>
                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>@lang('admin::app.tasks.status.completed')</option>
                                    <option value="cancelled" {{ $task->status === 'cancelled' ? 'selected' : '' }}>@lang('admin::app.tasks.status.cancelled')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error
                                    control-name="status" />
                            </x-admin::form.control-group>

                            <!-- Priority -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.edit.priority')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="priority"
                                    :value="old('priority', $task->priority)"
                                    :label="trans('admin::app.tasks.edit.priority')">
                                    <option value="">@lang('admin::app.tasks.priority.none')</option>
                                    <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>@lang('admin::app.tasks.priority.low')</option>
                                    <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>@lang('admin::app.tasks.priority.medium')</option>
                                    <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>@lang('admin::app.tasks.priority.high')</option>
                                    <option value="urgent" {{ $task->priority === 'urgent' ? 'selected' : '' }}>@lang('admin::app.tasks.priority.urgent')</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error
                                    control-name="priority" />
                            </x-admin::form.control-group>

                            <!-- Due Date -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.edit.due-date')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="date"
                                    name="due_date"
                                    :value="old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '')"
                                    :label="trans('admin::app.tasks.edit.due-date')" />

                                <x-admin::form.control-group.error
                                    control-name="due_date" />
                            </x-admin::form.control-group>

                            <!-- Assigned To -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.tasks.index.datagrid.assigned-to')
                                </x-admin::form.control-group.label>
                                @php
                                $assignedUserData = [];

                                $assignedTo = old('assigned_to', $task->assigned_to ?? null);

                                if ($assignedTo) {
                                $assignedUser = app('Webkul\User\Repositories\UserRepository')->find($assignedTo);
                                if ($assignedUser) {
                                $assignedUserData = [
                                'id' => $assignedUser->id,
                                'name' => $assignedUser->name,
                                ];
                                }
                                }

                                $assignedUserDataJson = json_encode($assignedUserData);
                                @endphp

                                    <x-admin::lookup
                                        src="{{ route('admin.settings.users.search') }}"
                                        name="assigned_to"
                                        :placeholder="trans('admin::app.components.lookup.click-to-add')"
                                        :value="$assignedUserDataJson" />

                                    <x-admin::form.control-group.error control-name="assigned_to" />

                                <x-admin::form.control-group.error
                                    control-name="assigned_to" />
                            </x-admin::form.control-group>
                        </div>
                    </div>
                </div>
            </div>
        </x-admin::form>
</x-admin::layouts>