<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';

    public const TUTOR = 'tutor';

    public const CONTENT_MANAGER = 'content_manager';

    public const PARENT = 'parent';

    public const STUDENT = 'student';

    protected $fillable = ['name', 'label', 'guard_name'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
