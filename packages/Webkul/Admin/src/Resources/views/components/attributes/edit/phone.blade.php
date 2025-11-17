@if (isset($attribute))
    <v-phone-component
        :attribute="{{ json_encode($attribute) }}"
        :validations="'{{ $validations }}'"
        :value="{{ json_encode(old($attribute->code) ?? $value) }}"
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
    </v-phone-component>
@endif

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-phone-component-template"
    >
        <template v-for="(contactNumber, index) in contactNumbers">
            <div class="mb-2 flex items-center">
                <x-admin::form.control-group.control
                    type="text"
                    ::id="attribute.code"
                    ::name="`${attribute['code']}[${index}][value]`"
                    class="rounded-r-none"
                    ::rules="getValidation"
                    ::label="attribute.name"
                    v-model="contactNumber['value']"
                    ::disabled="isDisabled"
                />

                <div class="relative w-48">
                    <Multiselect
                        v-model="contactNumber['label']"
                        :options="[
                            { label: '@lang('admin::app.common.custom-attributes.work')', value: 'work' },
                            { label: '@lang('admin::app.common.custom-attributes.home')', value: 'home' }
                        ]"
                        label="label"
                        value-prop="value"
                        placeholder="@lang('admin::app.common.custom-attributes.select-option')"
                        :searchable="false"
                        :close-on-select="true"
                    />
                </div>

                <i
                    v-if="contactNumbers.length > 1"
                    class="icon-delete ml-1 cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                    @click="remove(contactNumber)"
                ></i>
            </div>

            <x-admin::form.control-group.error ::name="`${attribute['code']}[${index}][value]`"/>

            <x-admin::form.control-group.error ::name="`${attribute['code']}[${index}].value`"/>
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
        app.component('v-phone-component', {
            template: '#v-phone-component-template',

            props: ['validations', 'isDisabled', 'attribute', 'value'],

            data() {
                return {
                    contactNumbers: this.value || [{'value': '', 'label': 'work'}],
                };
            },

            watch: {
                value(newValue, oldValue) {
                    if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
                        this.contactNumbers = newValue || [{'value': '', 'label': 'work'}];
                    }
                },
            },

            computed: {
                getValidation() {
                    return {
                        phone: true,
                        unique_contact_number: this.contactNumbers ?? [],
                        ...(this.validations === 'required' ? { required: true } : {}),
                    };
                },
            },

            created() {
                this.extendValidations();

                if (! this.contactNumbers || ! this.contactNumbers.length) {
                    this.contactNumbers = [{
                        'value': '',
                        'label': 'work'
                    }];
                }
            },

            methods: {
                add() {
                    this.contactNumbers.push({
                        'value': '',
                        'label': 'work'
                    });
                },

                remove(contactNumber) {
                    this.contactNumbers = this.contactNumbers.filter(number => number !== contactNumber);
                },

                extendValidations() {
                    defineRule('unique_contact_number', async (value, contactNumbers) => {
                        if (
                            ! value
                            || ! value.length
                        ) {
                            return true;
                        }

                        const phoneOccurrences = contactNumbers.filter(contactNumber => contactNumber.value === value).length;

                        if (phoneOccurrences > 1) {
                            return 'This phone number is already in use.';
                        }

                        /**
                         * Check if the phone number is unique. This support is only for person phone numbers only.
                         */
                         if (this.attribute.code === 'person[contact_numbers]') {
                            try {
                                const { data } = await this.$axios.get('{{ route('admin.settings.attributes.check_unique_validation') }}', {
                                    params: {
                                        entity_id: this.attribute.id,
                                        entity_type: 'persons',
                                        attribute_code: 'contact_numbers',
                                        attribute_value: value
                                    }
                                });

                                if (! data.validated) {
                                    return 'This phone number is already in use.';
                                }

                                return true;
                            } catch (error) {
                                console.error('Error checking email: ', error);

                                return 'Error validating email. Please try again.';
                            }
                        } else {
                            return true;
                        }
                    });
                },
            },
        });
    </script>
@endPushOnce
