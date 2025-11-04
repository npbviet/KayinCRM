<x-admin::layouts.anonymous>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.users.login.title')
    </x-slot>

    <div class="flex h-[100vh] flex-col items-center justify-center gap-10">
        <div class="flex flex-col items-center gap-5">
            <!-- Logo -->
            @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                <img
                    class="h-10 w-[110px]"
                    src="{{ Storage::url($logo) }}"
                    alt="{{ config('app.name') }}"
                />
            @else
                <img
                    class="w-max"
                    src="{{ vite()->asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                />
            @endif

            <div class="box-shadow flex min-w-[300px] flex-col rounded-md bg-white dark:bg-gray-900">
                {!! view_render_event('admin.sessions.login.form_controls.before') !!}

                <!-- Login Form -->
                <x-admin::form :action="route('admin.session.store')">
                    <p class="p-4 text-xl font-bold text-gray-800 dark:text-white">
                        @lang('admin::app.users.login.title')
                    </p>

                    <div class="border-y p-4 dark:border-gray-800">
                        <!-- Email -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.users.login.email')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                class="w-[254px] max-w-full"
                                id="email"
                                name="email"
                                rules="required|email"
                                :label="trans('admin::app.users.login.email')"
                                :placeholder="trans('admin::app.users.login.email')"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <!-- Password -->
                        <x-admin::form.control-group class="relative w-full">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.users.login.password')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="password"
                                class="w-[254px] max-w-full ltr:pr-10 rtl:pl-10"
                                id="password"
                                name="password"
                                rules="required|min:6"
                                :label="trans('admin::app.users.login.password')"
                                :placeholder="trans('admin::app.users.login.password')"
                            />

                            <span
                                class="icon-eye-hide absolute top-11 -translate-y-2/4 cursor-pointer text-2xl ltr:right-3 rtl:left-3"
                                onclick="switchVisibility()"
                                id="visibilityIcon"
                                role="presentation"
                                tabindex="0"
                            >
                            </span>

                            <x-admin::form.control-group.error control-name="password" />
                        </x-admin::form.control-group>
                    </div>

                    <div class="flex items-center justify-between p-4">
                        <!-- Forgot Password Link -->
                        <a
                            class="cursor-pointer text-xs font-semibold leading-6 text-brandColor"
                            href="{{ route('admin.forgot_password.create') }}"
                        >
                            @lang('admin::app.users.login.forget-password-link')
                        </a>

                        <!-- Submit Button -->
                        <button
                            class="primary-button"
                            aria-label="{{ trans('admin::app.users.login.submit-btn')}}"
                        >
                            @lang('admin::app.users.login.submit-btn')
                        </button>
                    </div>
                </x-admin::form>

                    {!! view_render_event('admin.sessions.login.form_controls.after') !!}

                    <!-- Social Login Buttons -->
                    <div class="border-t p-4 dark:border-gray-800">
                        <div class="mb-3 text-center text-sm text-gray-600 dark:text-gray-400">
                            @lang('admin::app.users.login.or-continue-with')
                        </div>

                        <div class="flex flex-col gap-2">
                            @if (config('services.google.client_id'))
                                <a
                                    href="{{ route('admin.social.redirect', 'google') }}"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    @lang('admin::app.users.login.continue-with-google')
                                </a>
                            @endif

                            @if (config('services.microsoft.client_id'))
                                <a
                                    href="{{ route('admin.social.redirect', 'microsoft') }}"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 23 23" fill="none">
                                        <path fill="#F25022" d="M0 0h11v11H0z"/>
                                        <path fill="#7FBA00" d="M12 0h11v11H12z"/>
                                        <path fill="#00A4EF" d="M0 12h11v11H0z"/>
                                        <path fill="#FFB900" d="M12 12h11v11H12z"/>
                                    </svg>
                                    @lang('admin::app.users.login.continue-with-microsoft')
                                </a>
                            @endif

                            @if (config('services.github.client_id'))
                                <a
                                    href="{{ route('admin.social.redirect', 'github') }}"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                    @lang('admin::app.users.login.continue-with-github')
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Powered By -->
        <div class="text-sm font-normal">
            @lang('admin::app.components.layouts.powered-by.description', [
                'krayin' => '<a class="text-brandColor hover:underline " href="https://krayincrm.com/">Krayin</a>',
                'webkul' => '<a class="text-brandColor hover:underline " href="https://webkul.com/">Webkul</a>',
            ]) 
        </div>
    </div>

    @push('scripts')
        <script>
            function switchVisibility() {
                let passwordField = document.getElementById("password");
                let visibilityIcon = document.getElementById("visibilityIcon");

                passwordField.type = passwordField.type === "password" ? "text" : "password";
                visibilityIcon.classList.toggle("icon-eye");
            }
        </script>
    @endpush
</x-admin::layouts.anonymous>
