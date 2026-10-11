<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Hospital;
use App\Models\Department;
use App\Models\AmbulanceCategory;
use App\Models\HospitalDepartment;

class HospitalMasterSampleSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            foreach (['ENT','Ophthalmology','Dentistry','General Medicine','General Surgery','Cardiology','Orthopaedics','Paediatrics','Obstetrics and Gynaecology','Dermatology','Neurology','Emergency Medicine'] as $name) {
                Department::firstOrCreate(['department_name'=>$name],['status'=>1]);
            }
            foreach (['Basic Life Support','Advanced Life Support','Patient Transport','Neonatal Ambulance','Air Ambulance'] as $name) {
                AmbulanceCategory::firstOrCreate(['name'=>$name],['status'=>1]);
            }
            foreach (['General Ward','ICU','NICU','PICU','Emergency','Private Room'] as $name) {
                if (!DB::table('bedding_type')->where('name',$name)->exists()) DB::table('bedding_type')->insert(['name'=>$name,'status'=>1,'created_at'=>now(),'created_by'=>0,'updated_at'=>now(),'updated_by'=>0]);
            }
            foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $name) {
                if (!DB::table('blood_type')->where('blood_type',$name)->exists()) DB::table('blood_type')->insert(['blood_type'=>$name,'status'=>1,'created_at'=>now(),'created_by'=>0,'updated_at'=>now(),'updated_by'=>0]);
            }
            foreach (['DEMO-HOSP-001'=>'Demo City Hospital','DEMO-HOSP-002'=>'Demo Community Hospital'] as $registration=>$name) {
                $hospital = Hospital::firstOrCreate(['registration_no'=>$registration],[
                    'hospital_name'=>$name,'short_name'=>$registration==='DEMO-HOSP-001'?'DEMO-CITY':'DEMO-COMM',
                    'type'=>'Sample','host'=>'demo','logo'=>'','state'=>0,'district'=>0,'city'=>0,
                    'address'=>'Sample hospital address','pin_code'=>0,'contact_no'=>'0000000000','emergency_no'=>'0000000000',
                    'email_id'=>'demo@example.invalid','website'=>'','geo_location'=>'','status'=>1,
                    'created_by'=>0,'updated_by'=>0,'accepting_requests'=>false,
                ]);
                foreach (Department::whereIn('department_name',['ENT','Ophthalmology','Dentistry','General Medicine'])->where('status',1)->get() as $department) {
                    HospitalDepartment::firstOrCreate(['hospital_id'=>$hospital->id,'department_id'=>$department->id],['status'=>1]);
                }
            }
        });
    }
}
