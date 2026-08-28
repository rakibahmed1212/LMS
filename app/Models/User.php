<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function permissions(): BelongsToMany
    {
        return $this->roles()->with('permissions');
    }

    public function hasRole(string|array $role): bool
    {
        return $this->roles->pluck('name')->intersect((array) $role)->isNotEmpty();
    }

    public function hasRoleOrPermission(string $role, string $permission): bool
    {
        if ($this->hasRole($role)) {
            return true;
        }

        return $this->roles->pluck('permissions')
            ->flatten()
            ->pluck('name')
            ->contains($permission);
    }

    /** Parent account holder: children registered under this user. */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
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
