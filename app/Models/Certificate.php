<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Academy;
class Certificate extends Model
{
    use HasFactory;
    protected $table = 'certificates';

    protected $fillable = [
    'academy_id', 'academy_name','student_name','course_level' , 'mark' ,'image' , 'receive_date', 'student_id' ,
    ];
    protected $hidden = [
         'created_at' , 'updated_at'
    ];

    public function academy() {
        return $this->belongsTo(Academy::class);
    }
    public function student(){
        return $this->belongsTo(Student::class);
    }

}