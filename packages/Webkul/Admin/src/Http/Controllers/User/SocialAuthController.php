<?php

namespace Webkul\Admin\Http\Controllers\User;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Laravel\Socialite\Facades\Socialite;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Menu\MenuItem;
use Webkul\User\Repositories\UserRepository;

class SocialAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        // For Microsoft, we need to specify tenant
        if ($provider === 'microsoft') {
            return Socialite::driver('microsoft')
                ->scopes(['openid', 'profile', 'email', 'User.Read'])
                ->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the OAuth provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            session()->flash('error', trans('admin::app.users.oauth-error'));

            return redirect()->route('admin.session.create');
        }

        // Find user by provider and provider_id
        $user = $this->userRepository->scopeQuery(function ($query) use ($provider, $socialUser) {
            return $query->where('provider', $provider)
                ->where('provider_id', $socialUser->getId());
        })->first();

        // If user doesn't exist, try to find by email
        if (! $user) {
            $user = $this->userRepository->scopeQuery(function ($query) use ($socialUser) {
                return $query->where('email', $socialUser->getEmail());
            })->first();

            // If user exists but doesn't have provider, update it
            if ($user) {
                $user->update([
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            } else {
                // Create new user (you may want to assign default role_id and status)
                $user = $this->userRepository->create([
                    'name'        => $socialUser->getName(),
                    'email'       => $socialUser->getEmail(),
                    'provider'    => $provider,
                    'provider_id' => $socialUser->getId(),
                    'image'       => $socialUser->getAvatar(),
                    'status'      => 1, // Active by default
                    'role_id'     => 1, // Default role - you may want to get this from config
                ]);
            }
        }

        // Check if user is active
        if ($user->status == 0) {
            session()->flash('warning', trans('admin::app.users.activate-warning'));

            return redirect()->route('admin.session.create');
        }

        // Login the user
        auth()->guard('user')->login($user, true);

        // Get menus and check permissions
        $menus = menu()->getItems('admin');

        $availableNextMenu = $menus?->first();

        if (! bouncer()->hasPermission('dashboard')) {
            if (is_null($availableNextMenu)) {
                session()->flash('error', trans('admin::app.users.not-permission'));

                auth()->guard('user')->logout();

                return redirect()->route('admin.session.create');
            }

            return redirect()->to($availableNextMenu->getUrl());
        }

        $intendedUrl = session()->get('url.intended', route('admin.dashboard.index'));

        $hasAccessToIntendedUrl = $this->canAccessIntendedUrl($menus, $intendedUrl);

        if ($hasAccessToIntendedUrl) {
            return redirect()->intended(route('admin.dashboard.index'));
        }

        return redirect()->to($availableNextMenu->getUrl());
    }

    /**
     * Validate that the provider is supported.
     */
    protected function validateProvider(string $provider): void
    {
        $supportedProviders = ['google', 'microsoft', 'github', 'facebook'];

        if (! in_array($provider, $supportedProviders)) {
            abort(404, trans('admin::app.users.oauth-provider-not-supported'));
        }
    }

    /**
     * Find menu item by URL.
     */
    protected function canAccessIntendedUrl(Collection $menus, ?string $url): ?MenuItem
    {
        if (is_null($url)) {
            return null;
        }

        foreach ($menus as $menu) {
            if ($menu->getUrl() === $url) {
                return $menu;
            }

            if ($menu->haveChildren()) {
                $found = $this->canAccessIntendedUrl($menu->getChildren(), $url);

                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }
}

