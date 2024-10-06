<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['player1', 'player2', 'board', 'current_turn', 'status', 'winner'];

    protected $casts = [
        'board' => 'array',
    ];
}
