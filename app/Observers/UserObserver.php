<?php

namespace App\Observers;

use Webkul\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    public function created(User $user)
    {
        // Nếu user được tạo qua social login (Google, Microsoft, GitHub, v.v.)
        // thì trường password sẽ là null hoặc là chuỗi rỗng đặc biệt
        if (empty($user->password) || $user->password === 'social-login-dummy-password') {
            // Gán role Viewer (thay 5 bằng ID role Viewer của bạn)
            $user->role_id = 6;
            $user->saveQuietly(); // save mà không trigger lại observer
        }
    }
}