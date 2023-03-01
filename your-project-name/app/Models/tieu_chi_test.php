<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tieu_chi_test extends Model
{
    use HasFactory;
    public $table = "tieu_chi_test";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'id_de_thi',
        'id_sheet',
        'add_start',
        'add_end',
        'data_reference',
        'flag',
        'content',
        'type_property',
        'point',
        'type'
    ];
}
