@php
    $viewPermissionOptions = [
        ['label' => trans('admin::app.settings.users.index.create.global'), 'value' => 'global'],
        ['label' => trans('admin::app.settings.users.index.create.group'), 'value' => 'group'],
        ['label' => trans('admin::app.settings.users.index.create.individual'), 'value' => 'individual'],
    ];
    $permissionTypeOptions = [
        ['label' => trans('admin::app.settings.roles.create.custom'), 'value' => 'custom'],
        ['label' => trans('admin::app.settings.roles.create.all'), 'value' => 'all'],
    ];
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.settings.users.index.edit.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs :name="'settings.users.edit.page'" :entity="$user" />
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.settings.users.index.edit.title')
                </div>
            </div>
        </div>

        <div id="edit-user-app">
            {{-- Truyền initial-user để Vue quản lý --}}
            <edit-user-form
                :roles='@json($roles)'
                :groups='@json($groups)'
                :initial-user='@json($user->toArray())'
            />
        </div>
    </div>

    @pushOnce('scripts')
        {{-- FORM CHÍNH --}}
        <script type="text/x-template" id="edit-user-form-template">
            <x-admin::form v-slot="{ meta, values, errors, handleSubmit }" as="div">
                <form @submit="handleSubmit($event, updateUser)" ref="userForm">
                    
                    {{-- Status --}}
                    <x-admin::form.control-group class="select-none flex items-center justify-end w-full">
                        <x-admin::form.control-group.label class="select-none !mb-0 mr-4">
                            @lang('admin::app.settings.users.index.create.status')
                        </x-admin::form.control-group.label>
                        <input type="hidden" name="name" value="0">
                        <x-admin::form.control-group.control
                            type="switch"
                            name="status"
                            value="1"
                            v-model="user.status_string"
                        />
                    </x-admin::form.control-group>

                    {{-- Name --}}
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.users.index.create.name')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="name"
                            v-model="user.name"
                            rules="required"
                        />
                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    {{-- Email --}}
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.users.index.create.email')
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="email"
                            name="email"
                            v-model="user.email"
                            rules="required"
                        />
                        <x-admin::form.control-group.error control-name="email" />
                    </x-admin::form.control-group>

                    {{-- Password --}}
                    <div class="flex gap-4 mt-4">
                        <x-admin::form.control-group class="flex-1">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.users.index.create.password')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="password"
                                name="password"
                                v-model="user.password"
                            />
                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="flex-1">
                            <x-admin::form.control-group.label>
                                @lang('admin::app.settings.users.index.create.confirm-password')
                            </x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="password"
                                name="confirm_password"
                                v-model="user.confirm_password"
                            />
                            <x-admin::form.control-group.error control-name="confirm_password" />
                        </x-admin::form.control-group>
                    </div>

                    {{-- Role + View Permission --}}
                    <div class="flex gap-4 mt-4">
                        <x-admin::form.control-group class="flex-1">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.users.index.create.role')
                            </x-admin::form.control-group.label>
                            
                            <Multiselect
                                v-model="user.role_id"
                                :options="roles.map(role => ({ label: role.name, value: role.id }))"
                                option-label="label"
                                option-value="value"
                                placeholder="@lang('admin::app.settings.users.index.create.role')"
                                :searchable="true"
                                :close-on-select="true"
                                rules="required"
                            />
                            <x-admin::form.control-group.error control-name="role_id" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="flex-1">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.users.index.create.view-permission')
                            </x-admin::form.control-group.label>
                            <Multiselect
                                v-model="view_permission"
                                :options="viewPermissionOptions"
                                option-label="label"
                                option-value="value"
                                :reduce="option => option.value"
                                :searchable="true"
                                :close-on-select="true"
                                rules="required"
                            />
                            <x-admin::form.control-group.error control-name="view_permission" />
                        </x-admin::form.control-group>
                    </div>

                    {{-- Group selection --}}
                    <div v-if="view_permission === 'group'" class="mt-4">
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.users.index.create.group')
                            </x-admin::form.control-group.label>
                            <Multiselect
                                v-model="user.groups"
                                :options="groups.map(g => ({ label: g.name, value: g.id }))"
                                mode="tags"
                                track-by="value"
                                label="label"
                            />
                            <x-admin::form.control-group.error control-name="groups[]" />
                        </x-admin::form.control-group>
                    </div>

                    {{-- Permission Type --}}
                    
                    {!! view_render_event('admin.settings.roles.edit.form.permission_type.before', ['role' => $user->role]) !!}

                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.roles.create.permissions')
                        </x-admin::form.control-group.label>
                        <Multiselect
                            v-model="permission_type"
                            :options="permissionTypeOptions"
                            option-label="label"
                            option-value="value"
                            placeholder="{{ trans('admin::app.common.select') }}"
                            :searchable="false"
                            :close-on-select="true"
                            rules="required"
                        />
                        <x-admin::form.control-group.error control-name="permission_type" />
                    </x-admin::form.control-group>

                    {!! view_render_event('admin.settings.roles.edit.form.permission_type.after', ['role' => $user->role]) !!}

                    <div class="flex gap-2.5 max-xl:flex-wrap mt-4">
                        {!! view_render_event('admin.settings.roles.edit.content.left.before', ['role' => $user->role]) !!}

                        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                            <div class="box-shadow rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                                <v-access-control 
                                    v-if="permission_type == 'custom'" 
                                    v-model="selected_permissions" 
                                    :key="user.role_id + '-' + permission_type" 
                                />
                            </div>
                        </div>

                        {!! view_render_event('admin.settings.roles.edit.content.left.after', ['role' => $user->role]) !!}
                    </div>

                    {!! view_render_event('admin.settings.roles.edit.content.after', ['role' => $user->role]) !!}

                    {{-- Save Button --}}
                    <div class="flex justify-end mt-6">
                        <x-admin::button
                            type="submit"
                            class="mt-6 primary-button"
                            :title="trans('admin::app.settings.users.index.create.save-btn')"
                            ::loading="isProcessing"
                            ::disabled="isProcessing"
                        />
                    </div>
                </form>
            </x-admin::form>
        </script>

        {{-- COMPONENT CON --}}
        <script type="text/x-template" id="v-access-control-template">
            <div>
                <x-admin::form.control-group.error control-name="permissions" />

                {!! view_render_event('admin.settings.roles.edit.form.tree_view.before', ['role' => $user->role]) !!}

                <x-admin::tree.view
                    input-type="checkbox"
                    value-field="key"
                    id-field="key"
                    :items="json_encode(acl()->getItems())"
                    v-bind:value="selectedPermissions"
                    @input="selectedPermissions = $event"
                    :fallback-locale="config('app.fallback_locale')"
                />

                {!! view_render_event('admin.settings.roles.edit.form.tree_view.after', ['role' => $user->role]) !!}
            </div>
        </script>

        <script type="module">
            app.component('edit-user-form', {
                template: '#edit-user-form-template',
                props: ['roles', 'groups', 'initialUser'],

                data() {
                    return {
                        isProcessing: false,
                        // Tạo bản sao user để vue track reactivity
                        user: { ...this.initialUser },
                        view_permission: 'global',
                        permission_type: 'custom',
                        selected_permissions: [],
                        viewPermissionOptions: @json($viewPermissionOptions),
                        permissionTypeOptions: @json($permissionTypeOptions),
                    };
                },

                created() {
                    this.prepareUserData();
                    this.setInitialPermissions();
                },

                methods: {
                    prepareUserData() {
                        this.user.status_string = String(this.user.status ?? 0);
                        this.view_permission = this.user.view_permission || 'global';

                        let initialGroups = this.user.groups;

                        if (! Array.isArray(initialGroups)) {
                            // Nếu không phải mảng (ví dụ: null), gán về mảng rỗng
                            initialGroups = []; 
                        }
                        
                        // Chuyển đổi mảng các đối tượng nhóm thành mảng các ID và loại bỏ giá trị rỗng
                        this.user.groups = initialGroups.map(g => {
                            return typeof g === 'object' && g !== null ? g.id : g;
                        }).filter(g => g !== undefined && g !== null);

                        // LOG ĐỂ BẠN KIỂM TRA TRONG CONSOLE:
                        console.log('Final user.groups for Multiselect:', this.user.groups);
                        
                        let customPerms = this.user.custom_permissions;

                        if (typeof customPerms === 'string' && customPerms.length > 0) {
                            try {
                                customPerms = JSON.parse(customPerms);
                            } catch (e) {
                                console.error("Failed to parse custom_permissions JSON:", e);
                                customPerms = [];
                            }
                        }

                        this.user.custom_permissions = Array.isArray(customPerms) ? customPerms : [];
                    },

                    // HÀM KHỞI TẠO PERMISSIONS BAN ĐẦU
                    setInitialPermissions() {
                        // 1. Ưu tiên Custom Permissions của User
                        const userCustomPermissions = this.user.custom_permissions; // Dùng this.user đã được chuẩn hóa

                        if (userCustomPermissions.length > 0) {
                            // ƯU TIÊN CUSTOM PERMISSIONS CỦA USER VÀ THIẾT LẬP permission_type LÀ 'custom'
                            this.selected_permissions = [...userCustomPermissions];
                            this.permission_type = 'custom';
                            console.log('Initial Permissions: Loaded from User Custom Permissions');
                            return;
                        }

                        // Nếu custom_permissions rỗng, kiểm tra Role của user
                        this.syncRolePermissions();
                        console.log('Initial Permissions: Loaded from Role Permissions');
                        
                    },

                    // HÀM ĐỒNG BỘ PERMISSIONS (CHỈ CHẠY KHI THAY ĐỔI ROLE HOẶC KHÔNG CÓ CUSTOM PERMS)
                    syncRolePermissions() {
                        const role = this.roles.find(r => r.id === this.user.role_id);
                        console.log('Role Data:', role);
                        
                        if (!role) {
                            this.permission_type = 'custom';
                            this.selected_permissions = [];
                            return;
                        }

                        this.permission_type = role.permission_type || 'custom';

                        if (this.permission_type === 'all') {
                            this.selected_permissions = [];
                        } else {
                            const perms = role.permissions;
                            this.selected_permissions = Array.isArray(perms) ? [...perms] : [];
                        }
                        
                        console.log('New Selected Permissions (Synced):', this.selected_permissions);
                    },

                    updateUser(_, { setErrors }) {
                        const form = new FormData(this.$refs.userForm);
                        form.set('role_id', this.user.role_id || '');
                        form.set('permission_type', this.permission_type);
                        form.set('view_permission', this.view_permission);
                        form.set('status', this.user.status_string === '1' ? 1 : 0);

                        // Xử lý Groups
                        form.delete('groups[]');
                        // Gửi mảng ID đã được Multiselect cập nhật
                        this.user.groups.forEach(groupId => form.append('groups[]', groupId));

                        // XÓA CẢ HAI TRƯỜNG TRƯỚC KHI THÊM
                        form.delete('permissions[]');
                        form.delete('custom_permissions[]'); 

                        if (this.permission_type === 'custom') {
                            // CHỈ GỬI custom_permissions[]
                            this.selected_permissions.forEach(p => form.append('custom_permissions[]', p));
                        } else {
                            // Nếu là 'all', gửi mảng rỗng cho custom_permissions để xóa dữ liệu cũ
                            form.append('custom_permissions[]', ''); 
                        }

                        form.append('_method', 'PUT');
                        this.isProcessing = true;
                        
                        // --- Dòng console.log để kiểm tra Form Data ---
                        console.log('--- Dữ liệu Form cuối cùng được gửi đi ---');
                        for (let [key, value] of form.entries()) {
                            console.log(`${key}: ${value}`);
                        }
                        // -------------------------------------

                        this.$axios.post(`{{ route('admin.settings.users.update', $user->id) }}`, form)
                            .then(res => {
                                this.$emitter.emit('add-flash', { type: 'success', message: res.data.message });
                                window.location.href = "{{ route('admin.settings.users.index') }}";
                            })
                            .catch(err => {
                                if (err.response?.status === 422) setErrors(err.response.data.errors);
                            })
                            .finally(() => this.isProcessing = false);
                    }
                },

                watch: {
                    'user.role_id'() {
                        this.syncRolePermissions();
                    }
                }
            });

            app.component('v-access-control', {
                template: '#v-access-control-template',
                props: ['modelValue', 'value'], 
                emits: ['update:modelValue', 'input'],
                computed: {
                    selectedPermissions: {
                        get() { 
                            return this.modelValue || this.value || []; 
                        },
                        set(v) { 
                            this.$emit('update:modelValue', v); 
                            this.$emit('input', v); 
                        }
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>