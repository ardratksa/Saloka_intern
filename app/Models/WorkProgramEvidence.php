<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkProgramEvidence extends Model
{
    use HasFactory;

    protected $fillable = [

        'work_program_id',

        'before_image',
        'after_image',

        'remark',

        'date',
    ];

    protected $casts = [

        'date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function workProgram()
    {
        return $this->belongsTo(WorkProgram::class);
    }
}