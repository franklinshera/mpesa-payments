<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents an instance of a payment `payments table`
 */
class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];   

    
    public function transaction(){
        return $this->belongsTo(Transaction::class);
    }
}