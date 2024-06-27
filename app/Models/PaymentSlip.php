<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSlip extends Model
{
    use HasFactory;

    protected $table = 'payment_slips';

    protected $fillable = ['name', 'email', 'governmentId', 'debtAmount', 'debtId', 'debtDueDate', 'status'];
}
