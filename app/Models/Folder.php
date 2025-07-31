<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['folder', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function folders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}