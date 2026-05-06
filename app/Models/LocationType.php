<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LocationType extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "LocationType {$e}");
    }

    public function locationNames()
    {
        return $this->hasMany(LocationName::class, 'location_type_id');
    }

    public function masterJobs()
    {
        return $this->hasMany(MasterJob::class, 'location_type_id');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'tipe_id');
    }
}