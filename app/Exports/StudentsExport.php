<?php

namespace App\Exports;

use App\Models\student;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use mysql_xdevapi\Collection;

class StudentsExport implements FromQuery
{
    use Exportable;
    public $students;

    public function __construct(\Illuminate\Database\Eloquent\Collection $students){

        $this->students = $students;
    }

   public function query()
   {
      return Student::whereKey($this->students->pluck('id'))->select('name','email');
   }
}
