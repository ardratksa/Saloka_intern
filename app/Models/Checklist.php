<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Checklist extends Model
{
    use LogsActivity;

    protected $fillable = [
        'date',
        'job_id',
        'periode_id',
        'tipe_id',
        'location_id',
        'user_id',
        'status',
        'note',
        'pic',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'date', 'status', 'note', 'pic',
                'job_id', 'periode_id', 'location_id',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Checklist {$e}");
    }

    public function job()
    {
        return $this->belongsTo(MasterJob::class, 'job_id');
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'periode_id');
    }

    public function locationType()
    {
        return $this->belongsTo(LocationType::class, 'tipe_id');
    }

    public function location()
    {
        return $this->belongsTo(LocationName::class, 'location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentations()
    {
        return $this->hasMany(ChecklistDocumentation::class);
    }

    public function issue()
    {
        return $this->hasOne(Issue::class);
    }
}