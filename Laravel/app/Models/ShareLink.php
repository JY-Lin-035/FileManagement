<?php

namespace App\Models;

use App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShareLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'link',
        'path',
        'fileName',
        'create_at',
        'updated_at',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class, 'owner_id', 'id');
    }
}
