<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScReportPhoto extends Model
{
    protected $fillable = [
        'sc_report_id',
        'phase',
        'photo_path',
    ];

    public function report()
    {
        return $this->belongsTo(ScReport::class, 'sc_report_id');
    }
}