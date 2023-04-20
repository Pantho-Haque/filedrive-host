<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'folder_id',
        'file_name',
        'file_link',
        'file_size',
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}
