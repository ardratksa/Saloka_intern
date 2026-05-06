<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkPlan extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'location_id',
        'name',
        'type',
        'duration_estimate',
        'planned_start',
        'notes',
        'status',
    ];

    protected $casts = [
        'planned_start' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'status', 'planned_start'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "WorkPlan {$e}");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(LocationName::class, 'location_id');
    }
}