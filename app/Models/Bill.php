<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bill';

    protected $casts = [
        'CUSTOMER' => 'json', // Assuming 'customer' is a JSON attribute
        'DETAILS' => 'json',  // Assuming 'details' is a JSON attribute
    ];

    public function getDetailsAttribute($value)
    {
        // Handle the retrieval of the 'details' attribute
        // You may need to convert it from its raw form to a format suitable for your application
        return json_decode($value, true); // Example for a JSON attribute
    }

    public function setDetailsAttribute($value)
    {
        // Handle the storage of the 'details' attribute
        // You may need to convert it to the raw format expected by Oracle
        $this->attributes['details'] = json_encode($value); // Example for a JSON attribute
    }


    public function details()
    {
        return $this->hasOne(BillDetail::class); // Adjust the relationship based on your actual structure
    }
}
