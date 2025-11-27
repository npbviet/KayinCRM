@if (isset($attribute))
@php
    $recordId = view()->shared('current_person_id', null);
    $recordId = is_numeric($recordId) ? (int) $recordId : null;
@endphp
    <v-email-component
        :attribute="{{ json_encode($attribute) }}"
        :validations="'{{ $validations }}'"
        :value="{{ json_encode(old($attribute->code) ?? $value) }}"
        :record-id="{{ json_encode($recordId) }}"
    >
        <div class="mb-2 flex items-center">
            <input
                type="text"
                class="w-full rounded rounded-r-none border border-gray-200 px-2.5 py-2 text-sm font-normal text-gray-800 hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
            >
        </div>

        <span class="flex cursor-pointer items-center gap-2 text-brandColor">
            <i class="icon-add text-md !text-brandColor"></i>

            @lang("admin::app.common.custom-attributes.add-more")
        </span>
    </v-email-component>
@endif

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-email-component-template"
    >
        <template v-for="(email, index) in emails">
            <div class="mb-2 flex items-center">
                <!-- Input Value -->
                <x-admin::form.control-group.control
                    type="text"
                    ::id="attribute.code"
                    ::name="`${attribute['code']}[${index}][value]`"
                    class="rounded-r-none"
                    ::rules="getValidation"
                    ::label="attribute.name"
                    v-model="email['value']"
                    ::disabled="isDisabled"
                />

                <!-- FIX: Thêm Input Hidden cho Label để submit form được -->
                <input 
                    type="hidden" 
                    :name="`${attribute['code']}[${index}][label]`" 
                    :value="email['label']"
                />

                <div class="relative w-48">
                    <!-- FIX: Đổi v-model từ emails[0] thành email['label'] và dùng computed options -->
                    <Multiselect
                        v-model="email['label']"
                        :options="labelOptions"
                        label="label"
                        value-prop="value"
                        placeholder="@lang('admin::app.common.custom-attributes.select')"
                        :searchable="false"
                        :close-on-select="true"
                    />
                </div>

                <i
                    v-if="emails.length > 1"
                    class="icon-delete ml-1 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                    @click="remove(email)"
                ></i>
            </div>

            <!-- Error components -->
            <x-admin::form.control-group.error ::name="`${attribute['code']}[${index}][value]`"/>
            <x-admin::form.control-group.error ::name="`${attribute['code']}[${index}][label]`"/>
        </template>

        <span
            class="flex w-fit cursor-pointer items-center gap-2 text-brandColor"
            @click="add"
            v-if="! isDisabled"
        >
            <i class="icon-add text-md !text-brandColor"></i>

            @lang("admin::app.common.custom-attributes.add-more")
        </span>
    </script>

    <script type="module">
        app.component('v-email-component', {
            template: '#v-email-component-template',

            props: ['validations', 'isDisabled', 'attribute', 'value', 'recordId'],

            data() {
                return {
                    emails: this.value && this.value.length ? this.value : [{'value': '', 'label': 'work'}],
                };
            },

            watch: {
                value(newValue, oldValue) {
                    if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
                        this.emails = newValue && newValue.length ? newValue : [{'value': '', 'label': 'work'}];
                    }
                },
            },

            computed: {
                getValidation() {
                    return {
                        email: true,
                        unique_email: true,
                        ...(this.validations === 'required' ? { required: true } : {}),
                    };
                },

                labelOptions() {
                    return [
                        { label: "@lang('admin::app.common.custom-attributes.work')", value: 'work' },
                        { label: "@lang('admin::app.common.custom-attributes.home')", value: 'home' }
                    ];
                }
            },

            created() {
                this.extendValidations();
                console.log('Email Component - Current Person ID:', this.recordId);
            },

            methods: {
                add() {
                    this.emails.push({
                        'value': '',
                        'label': 'work'
                    });
                },

                remove(email) {
                    this.emails = this.emails.filter(item => item !== email);
                },

                extendValidations() {
                    defineRule('unique_email', async (value, emails) => {   // THAM SỐ THỨ 2 emails – GIỐNG HỆT PHONE
                        if (!value || !value.length) return true;

                        // Check trùng trong form (frontend) – giống hệt phone
                        const emailOccurrences = emails.filter(emailItem => emailItem.value === value).length;
                        if (emailOccurrences > 1) {
                            return 'This email is duplicated in the form.';
                        }

                        // Check server-side
                        try {
                            const { data } = await this.$axios.get('{{ route('admin.settings.attributes.check_unique_validation') }}', {
                                params: {
                                    entity_id: this.recordId,     // Đúng – null khi tạo mới, số khi sửa
                                    entity_type: 'persons',
                                    attribute_code: 'emails',
                                    attribute_value: value
                                }
                            });

                            if (!data.validated) {
                                return 'This email is already in use.';
                            }

                            return true;
                        } catch (error) {
                            console.error('Error checking email uniqueness:', error);
                            return true; // Không block user khi API lỗi
                        }
                    });
                },
            },
        });
    </script>
@endPushOnce