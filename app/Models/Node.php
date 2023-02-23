<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    public function origins()
    {
        return $this->hasMany(Connection::class, 'origin');
    }
    public function destinations()
    {
        return $this->hasMany(Connection::class, 'destination');
    }
}
