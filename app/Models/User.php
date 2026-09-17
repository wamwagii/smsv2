<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password'])]   // is_admin removed
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements
    FilamentUser,
    MustVerifyEmail,
    HasAppAuthentication,
    HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    /* -----------------------------------------------------------------
     |  Filament
     | -----------------------------------------------------------------
     */

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /* -----------------------------------------------------------------
     |  Boot — guard against locking yourself out
     | -----------------------------------------------------------------
     */

    protected static function booted(): void
    {
        static::updating(function (User $user) {
            if (
                $user->isDirty('is_admin')
                && $user->getOriginal('is_admin')
                && ! $user->is_admin
            ) {
                DB::transaction(function () {
                    if (static::where('is_admin', true)->lockForUpdate()->count() <= 1) {
                        throw new \RuntimeException(
                            'Cannot remove the admin role from the last administrator. '
                            . 'Promote another user to admin first.'
                        );
                    }
                });
            }
        });

        static::deleting(function (User $user) {
            if ($user->is_admin) {
                DB::transaction(function () {
                    if (static::where('is_admin', true)->lockForUpdate()->count() <= 1) {
                        throw new \RuntimeException(
                            'Cannot delete the last administrator. '
                            . 'Promote another user to admin first.'
                        );
                    }
                });
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

    /* -----------------------------------------------------------------
     |  Static helpers
     | -----------------------------------------------------------------
     */

    public static function current(): ?self
    {
        $user = Auth::user();

        return $user instanceof self ? $user : null;
    }

    public static function currentIsAdmin(): bool
    {
        return static::current()?->isAdmin() ?? false;
    }

    public static function currentId(): ?int
    {
        return static::current()?->id;
    }

    public static function isLastAdmin(?self $user = null): bool
    {
        if ($user && ! $user->is_admin) {
            return false;
        }

        return static::where('is_admin', true)->count() <= 1;
    }
}