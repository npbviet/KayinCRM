<x-admin::layouts>
        <x-slot:title>
            @lang('admin::app.tasks.index.title')
        </x-slot>

        <!-- Header -->
        {!! view_render_event('admin.tasks.index.header.before') !!}

        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            {!! view_render_event('admin.tasks.index.header.left.before') !!}

            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                <x-admin::breadcrumbs name="tasks" />

                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.tasks.index.title')
                </div>
            </div>

            {!! view_render_event('admin.tasks.index.header.left.after') !!}

            {!! view_render_event('admin.tasks.index.header.right.before') !!}

            <div class="flex items-center gap-x-2.5">
                <!-- Create button -->
                @if (bouncer()->hasPermission('tasks.create'))
                <a
                    href="{{ route('admin.tasks.create') }}"
                    class="primary-button">
                    @lang('admin::app.tasks.index.create-btn')
                </a>
                @endif
            </div>

            {!! view_render_event('admin.tasks.index.header.right.after') !!}
        </div>

        {!! view_render_event('admin.tasks.index.header.after') !!}

        {!! view_render_event('admin.tasks.index.content.before') !!}

        <!-- Content -->
        <div class="mt-3.5">
            <x-admin::datagrid :src="route('admin.tasks.index')">
                <!-- Custom Search Slot -->
                <x-slot:search>
                    @include('admin::tasks.index.search')
                </x-slot>
            </x-admin::datagrid>
        </div>

        {!! view_render_event('admin.tasks.index.content.after') !!}


</x-admin::layouts>