<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Issue extends Model
{
    use LogsActivity;

    protected $fillable = [
        'checklist_id',
        'user_id',
        'location_id',
        'date',
        'type',
        'description',
        'status',
        'wa_sent',
    ];

    protected $casts = [
        'date'    => 'date',
        'wa_sent' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'status', 'wa_sent', 'description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $e) => "Issue {$e}");
    }

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(LocationName::class, 'location_id');
    }

    public function documentations()
    {
        return $this->hasMany(IssueDocumentation::class);
    }
}