<?php

namespace Webkul\Admin;

use Webkul\User\Repositories\UserRepository;

class Bouncer
{
    /**
     * Checks if user allowed or not for certain action
     *
     * @param  string  $permission
     * @return void
     */
    public function hasPermission($permission)
    {
        $user = auth()->guard('user')->user();
        
        if (! $user) {
            return false;
        }
        // 1. Nếu role có full quyền → cho qua mọi thứ
        if ($user->role->permission_type == 'all') {
            return true;
        }
    
        // 2. Nếu user có custom_permissions → dùng nó làm nguồn quyền DUY NHẤT
        if (! empty($user->custom_permissions)) {
            $customPermissions = is_array($user->custom_permissions)
                ? $user->custom_permissions
                : json_decode($user->custom_permissions, true);
    
            // Đảm bảo là array và kiểm tra
            return is_array($customPermissions) && in_array($permission, $customPermissions);
        }
    
        // 3. Nếu không có custom_permissions → mới dùng quyền của role
        $rolePermissions = $user->role->permissions ?? [];
    
        return in_array($permission, $rolePermissions);
    }
    /**
     * Checks if user allowed or not for certain action
     *
     * @param  string  $permission
     * @return void
     */
    public static function allow($permission)
    {
        if (! auth()->guard('user')->check() || ! auth()->guard('user')->user()->hasPermission($permission)) {
            abort(401, 'This action is unauthorized');
        }
    }

    /**
     * This function will return user ids of current user's groups
     *
     * @return array|null
     */
    public function getAuthorizedUserIds()
    {
        $user = auth()->guard('user')->user();

        if ($user->view_permission == 'global') {
            return null;
        }

        if ($user->view_permission == 'group') {
            return app(UserRepository::class)->getCurrentUserGroupsUserIds();
        } else {
            return [$user->id];
        }
    }
}
