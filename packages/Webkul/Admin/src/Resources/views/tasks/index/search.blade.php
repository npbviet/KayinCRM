{!! view_render_event('admin.tasks.index.search.before') !!}

<v-task-search></v-task-search>

{!! view_render_event('admin.tasks.index.search.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-task-search-template"
    >
        <div class="relative flex max-w-[445px] items-center max-md:w-full max-md:max-w-full">
            <div class="icon-search absolute top-1.5 flex items-center text-2xl ltr:left-3 rtl:right-3"></div>

            <input
                type="text"
                name="search"
                class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400 ltr:pl-10 ltr:pr-3 rtl:pl-3 rtl:pr-10"
                placeholder="@lang('admin::app.tasks.index.search.placeholder')"
                autocomplete="off"
                :value="getSearchedValues()"
                @keyup.enter="search"
            >
        </div>
    </script>

    <script type="module">
        app.component('v-task-search', {
            template: '#v-task-search-template',

            data() {
                return {
                    filters: {
                        columns: [],
                    },
                };
            },

            computed: {
                isLoading() {
                    return this.$parent?.$props?.isLoading || false;
                },
                available() {
                    return this.$parent?.$props?.available || {};
                },
                applied() {
                    return this.$parent?.$props?.applied || { filters: { columns: [] } };
                },
            },

            mounted() {
                this.filters.columns = this.applied.filters.columns.filter((column) => column.index === 'all');
            },

            methods: {
                /**
                 * Perform a search operation based on the input value.
                 * Search will be processed by TaskDataGrid with custom logic.
                 *
                 * @param {Event} $event
                 * @returns {void}
                 */
                search($event) {
                    let requestedValue = $event.target.value;

                    let appliedColumn = this.filters.columns.find(column => column.index === 'all');

                    if (! requestedValue) {
                        if (appliedColumn) {
                            appliedColumn.value = [];
                        }

                        // Call parent's search method
                        if (this.$parent && this.$parent.search) {
                            this.$parent.search({ columns: this.filters.columns });
                        }

                        return;
                    }

                    if (appliedColumn) {
                        appliedColumn.value = [requestedValue];
                    } else {
                        this.filters.columns.push({
                            index: 'all',
                            value: [requestedValue]
                        });
                    }

                    // Call parent's search method
                    if (this.$parent && this.$parent.search) {
                        this.$parent.search({ columns: this.filters.columns });
                    }
                },

                /**
                 * Get the searched values for the search input.
                 *
                 * @returns {string}
                 */
                getSearchedValues() {
                    let appliedColumn = this.filters.columns.find(column => column.index === 'all');

                    return appliedColumn?.value?.[0] ?? '';
                },
            },
        });
    </script>
@endPushOnce
