<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Period extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'time_start',
        'time_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'time_start', 'time_end', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Period {$e}");
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'periode_id');
    }
}