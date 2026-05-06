<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistDocumentation extends Model
{
    protected $fillable = [
        'checklist_id',
        'image',
        'note',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
}