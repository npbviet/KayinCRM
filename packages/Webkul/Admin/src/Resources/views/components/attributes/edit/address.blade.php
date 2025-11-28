@if (isset($attribute))
    <v-address-component
        :attribute='@json($attribute)'
        :validations="'{{ $validations }}'"
        :data='@json(old($attribute->code) ?: $value)'
    >
        <!-- Addresses Shimmer -->    
        <x-admin::shimmer.common.address />
    </v-address-component>
@endif

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-address-component-template"
    >
        <div class="flex gap-4 max-md:flex-wrap">
            <div class="w-full">
                <!-- Address (Textarea field) -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.control
                        type="textarea"
                        ::name="attribute['code'] + '[address]'"
                        rows="10"
                        ::value="data ? data['address'] : ''"
                        :label="trans('admin::app.common.custom-attributes.address')"
                        ::rules="attribute.is_required ? 'required|' + validations : validations"
                    />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '[address]'" />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '.address'" />
                </x-admin::form.control-group>
            </div>

            <div class="grid w-full">
                <!-- Country Field -->
                <x-admin::form.control-group>
                    <Multiselect
                        v-model="country"
                        :options='@json(
                            collect(core()->countries())->map(fn($c) => [
                                "label" => $c->name,
                                "value" => $c->code
                            ])->values()
                        )'
                        option-label="label"
                        option-value="value"
                        placeholder="@lang('admin::app.common.custom-attributes.select-country')"
                        :searchable="true"
                        :close-on-select="true"
                        :rules="attribute.is_required ? 'required|' + validations : validations"
                    />
                    <input type="hidden" :name="attribute['code'] + '[country]'" :value="country">
                    <x-admin::form.control-group.error ::name="attribute['code'] + '[country]'" />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '.country'" />
                </x-admin::form.control-group>

                <!-- State Field -->
                <template v-if="haveStates()">
                    <x-admin::form.control-group>
                        <Multiselect
                            v-model="state"
                            :options="countryStates[country]"
                            option-label="label"
                            option-value="value"
                            :placeholder="'{{ trans('admin::app.common.custom-attributes.select-state') }}'"
                            :searchable="true"
                            :close-on-select="true"
                            :rules="attribute.is_required ? 'required|' + validations : validations"
                        />
                        <input type="hidden" :name="attribute['code'] + '[state]'" :value="state">

                        <x-admin::form.control-group.error ::name="attribute['code'] + '[state]'" />

                        <x-admin::form.control-group.error ::name="attribute['code'] + '.state'" />
                    </x-admin::form.control-group>
                </template>

                <template v-else>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.control
                            type="text"
                            ::name="attribute['code'] + '[state]'"
                            :placeholder="trans('admin::app.common.custom-attributes.state')"
                            :label="trans('admin::app.common.custom-attributes.state')"
                            ::rules="attribute.is_required ? 'required|' + validations : validations"
                            v-model="state"

                        >
                        </x-admin::form.control-group.control>
                        
                        <x-admin::form.control-group.error ::name="attribute['code'] + '[state]'" />

                        <x-admin::form.control-group.error ::name="attribute['code'] + '.state'" />
                    </x-admin::form.control-group>
                </template>

                <!-- City Field -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.control
                        type="text"
                        ::name="attribute['code'] + '[city]'"
                        ::value="data && data['city'] ? data['city'] : ''"
                        :placeholder="trans('admin::app.common.custom-attributes.city')"
                        :label="trans('admin::app.common.custom-attributes.city')"
                        ::rules="attribute.is_required ? 'required|' + validations : validations"
                    />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '[city]'"/>

                    <x-admin::form.control-group.error ::name="attribute['code'] + '.city'" />
                </x-admin::form.control-group>

                <!-- Postcode Field -->
                <x-admin::form.control-group>
                    <x-admin::form.control-group.control
                        type="text"
                        ::name="attribute['code'] + '[postcode]'"
                        ::value="data &&  data['postcode'] ? data['postcode'] : ''"
                        :placeholder="trans('admin::app.common.custom-attributes.postcode')"
                        :label="trans('admin::app.common.custom-attributes.postcode')"
                        ::rules="attribute.is_required ? 'required|postcode' : 'postcode'"
                    />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '[postcode]'" />

                    <x-admin::form.control-group.error ::name="attribute['code'] + '.postcode'" />
                </x-admin::form.control-group>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-address-component', {
            template: '#v-address-component-template',

            props: ['attribute', 'data', 'validations'],

            data() {    

                const countries = JSON.parse('@json(core()->groupedStatesByCountries())');
                console.log(countries);
                const states = {};
                Object.keys(countries).forEach(countryCode => {
                    states[countryCode] = countries[countryCode].map(state => {
                        return {
                            label: state.name,
                            value: state.code
                        }
                    });
                });
             
                return {
                    country: this.data?.country || '',

                    state: this.data?.state || '',

                    countryStates: states,
                };
            },
            
            methods: {
                haveStates() {
                    /*
                    * The double negation operator is used to convert the value to a boolean.
                    * It ensures that the final result is a boolean value,
                    * true if the array has a length greater than 0, and otherwise false.
                    */
                    return !!this.countryStates[this.country]?.length;
                },
            }
        });
    </script>
@endPushOnce