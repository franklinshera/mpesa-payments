<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Represents an instance of a transaction `transactions table`
 */
class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];



    public function payment(){
        return $this->hasOne(Payment::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }


    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query){
        return $query->where('Status',1);
    }


    /**
     * Check if transaction is completed
     */
    public function isCompleted(){
        return $this->Status == '1';
    }
}