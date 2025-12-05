<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoRoom extends Model {
    use HasFactory;

    protected $fillable = ['room_id', 'creator_id', 'participants', 'is_active'];

    protected $casts = [
        'participants' => 'array',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }
}