<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExampleManagement extends Model
{
    use SoftDeletes;

    protected $table = 'example_management';

    protected $fillable = [
        'name',
    ];
}
