<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, HasPermissions, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid', 'email', 'password_hash', 'first_name', 'last_name',
        'phone', 'avatar_url', 'role', 'granted_permissions', 'is_active', 'is_verified',
        'locale', 'last_login_at', 'email_verified_at',
    ];

    protected $hidden = ['password_hash'];

    protected $authPasswordName = 'password_hash';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_hash' => 'hashed',
            'granted_permissions' => 'array',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (User $user) => $user->uuid ??= (string) Str::uuid());
    }

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function passwordResets()
    {
        return $this->hasMany(PasswordReset::class);
    }

    public function newsletterSubscription()
    {
        return $this->hasOne(NewsletterSubscriber::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name, 0, 1));
    }
}
