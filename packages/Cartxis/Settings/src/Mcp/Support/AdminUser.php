<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Support;

use App\Models\User;

final class AdminUser
{
    public static function isAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $roleIsAdmin = (string) ($user->role ?? '') === 'admin';
        $permissionsIsAdmin = method_exists($user, 'isAdmin') ? (bool) $user->isAdmin() : false;

        return $roleIsAdmin || $permissionsIsAdmin;
    }
}
