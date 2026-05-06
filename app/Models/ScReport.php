<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ScReport extends Model
{
    use LogsActivity;

    protected $fillable = [
        'task_name',
        'week_label',
        'week_start',
        'pic_user_id',
        'pic_name',
        'notes',
        'status',
    ];

    protected $casts = [
        'week_start' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_name', 'pic_name', 'status', 'notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "ScReport {$e}");
    }

    public function photos()
    {
        return $this->hasMany(ScReportPhoto::class, 'sc_report_id');
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function getPhotoByPhase(string $phase): ?ScReportPhoto
    {
        return $this->photos()->where('phase', $phase)->first();
    }
}