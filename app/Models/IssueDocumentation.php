<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueDocumentation extends Model
{
    protected $fillable = [
        'issue_id',
        'image',
        'note',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}