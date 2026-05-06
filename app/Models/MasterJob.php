<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MasterJob extends Model
{
    use LogsActivity;

    protected $table = 'master_jobs';

    protected $fillable = [
        'location_type_id',
        'job',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['job', 'order', 'is_active', 'location_type_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "MasterJob {$e}");
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'job_id');
    }
}