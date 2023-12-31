<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'section_id',
        'class_id',
        'address'
    ];
    public function Section(){
        return $this->belongsTo(Section::class);
    }
    public  function Class(){
        return $this->belongsTo(Classes::class);
    }
}
