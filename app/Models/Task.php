<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'status',
        'priority',
        'description',
        'parent_id',
        'created_at',
        'completed_at'
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subtasks()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
