<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory;
    public $table = "criteria";

    public function __construct()
    {
        $this->point = 0;
        $this->flag = true;

    }
    protected $fillable = [
        'name',
        'point',
        'flag',
    ];

    // protected $attributes = [
    //     'flag' => true,
    //     'point'=> 0,

    // ];
}
