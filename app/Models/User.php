<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'locale',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function allPermissions(): Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function hasRole(string|array $role): bool
    {
        return $this->roles()
            ->whereIn('name', (array) $role)
            ->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    public function hasRoleOrPermission(string $role, string $permission): bool
    {
        if ($this->hasRole($role)) {
            return true;
        }

        return $this->hasPermission($permission);
    }

    /** Parent account holder: children registered under this user. */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** Tutor profile: courses assigned to this staff member. */
    public function tutorAssignments(): HasMany
    {
        return $this->hasMany(TutorAssignment::class, 'tutor_id');
    }

    public function assignedStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'tutor_student', 'tutor_id', 'student_id');
    }

    public function liveClasses(): HasMany
    {
        return $this->hasMany(LiveClass::class, 'tutor_id');
    }
}
