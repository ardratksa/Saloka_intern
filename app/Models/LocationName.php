<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationName extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'location_type_id',
        'name',
        'qr_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active', 'location_type_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "LocationName {$e}");
    }

    public function type()
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'location_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'location_id');
    }

    public function workPlans()
    {
        return $this->hasMany(WorkPlan::class, 'location_id');
    }
}