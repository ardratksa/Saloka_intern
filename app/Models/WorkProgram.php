<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkProgram extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'location_type_id',

        'area_id',
        'area_name',
        'location_name',
        'sub_location',

        'job_id',

        'category',
        'plan',

        'how_to_do',
        'time_range',
        'pic',

        'month',
        'year',
        'scheduled_dates',

        'status',
        'has_evidence',
        'completed_at',

        'checker',
        'remark',
    ];

    protected $casts = [

        'scheduled_dates' => 'array',

        'completed_at' => 'datetime',

        'has_evidence' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function job()
    {
        return $this->belongsTo(MasterJob::class, 'job_id');
    }

    public function evidences()
    {
        return $this->hasMany(
            WorkProgramEvidence::class,
            'work_program_id'
        );
    }
}