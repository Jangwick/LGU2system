<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_code',
        'student_name',
        'blood_type',
        'blood_test',
        'urine_test',
        'color',
        'transparency',
        'rbc',
        'wbc',
        'platelet',
        'neutrophils',
        'lymphocytes',
        'eosinophils',
        'basophils',
        'protein',
        'specific_gravity',
        'glucose',
        'hemoglobin',
        'hematocrit',
        'monocytes',
    ];

    public function getRouteKeyName()
    {
        return 'test_code';
    }
}
