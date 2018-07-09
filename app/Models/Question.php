<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    const SELECT = 'select';
    const CHECKBOX = 'checkbox';
    const DATE = 'date';
    const TEXT = 'text';
}
