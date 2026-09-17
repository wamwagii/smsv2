<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    /* -----------------------------------------------------------------
     |  Boot — guard against locking yourself out
     | -----------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::updating(function (User $user) {
            // If the is_admin flag is being changed FROM true TO false,
            // and this is the last remaining admin, block it.
            if (
                $user->isDirty('is_admin')
                && $user->getOriginal('is_admin')
                && ! $user->is_admin
                && static::where('is_admin', true)->count() <= 1
            ) {
                throw new \RuntimeException(
                    'Cannot remove the admin role from the last administrator. '
                    . 'Promote another user to admin first.'
                );
            }
        });

        static::deleting(function (User $user) {
            // Prevent deleting the last remaining admin.
            if (
                $user->is_admin
                && static::where('is_admin', true)->count() <= 1
            ) {
                throw new \RuntimeException(
                    'Cannot delete the last administrator. '
                    . 'Promote another user to admin first.'
                );
            }
        });
    }

    /* -----------------------------------------------------------------
     |  Instance helpers
     | -----------------------------------------------------------------
     */

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasRole(string $role): bool
    {
        return $role === 'admin' && (bool) $this->is_admin;
    }

    /* -----------------------------------------------------------------
     |  Static helpers — typed for IDE + static analysis
     | -----------------------------------------------------------------
     */

    /**
     * The currently authenticated user, or null.
     */
    public static function current(): ?self
    {
        $user = Auth::user();

        return $user instanceof self ? $user : null;
    }

    /**
     * Whether the currently authenticated user is an admin.
     */
    public static function currentIsAdmin(): bool
    {
        return static::current()?->isAdmin() ?? false;
    }

    /**
     * ID of the currently authenticated user, or null.
     */
    public static function currentId(): ?int
    {
        return static::current()?->id;
    }

    /**
     * Whether the given user is the last remaining admin.
     */
    public static function isLastAdmin(?self $user = null): bool
    {
        if ($user && ! $user->is_admin) {
            return false;
        }

        return static::where('is_admin', true)->count() <= 1;
    }
}