<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CsvController extends Controller
{
    //
    public function download_csv(Request $request) {

        $page = $request->page_name;

        if($page == 'college') {
            $output = "university_id,college_name,short_name,college_id,website_url,principal_name,contact_no,contact_no2,email_id,address,state,remarks\n";
    
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=College.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;    
        }
        elseif ($page == 'department') {
            $output = "university_id,department_name,website_url,hod_name,contact_no,contact_no2,email_id,address,state,remarks\n";
    
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Department.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;    
        }
        
        elseif ($page == 'faculty') {
            $output = "college_id,department_id,firstname,lastname,whatsapp_no,email_id,gender,group\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Faculty.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        elseif ($page == 'course') {
            $output = "college_id,department_id,code,name,duration\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Course.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        elseif ($page == 'subject') {
            $output = "unique_paper_code,department,subject_name,subject_type,credit,total_marks\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Subject.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        elseif ($page == 'rollno') {
            $output = "roll_no,form_no,semester\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Roll No.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;

        }
        elseif ($page == 'student_data') {
            $output = "academic_year,admission_year,course_id,semester,form_no,name,category,gender,mobile_no,email,reference_no,round,cuet_score,kashmiri_migrant,pwd_category,minority,cw_quota,ward_quota\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Student Data.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        elseif ($page == 'admission') {
            $output = "round,reference_no,login_id,name,score,category,gender,kashmiri_migrant,pwd_category,minority,cw_quota,ward_quota,mobile,email,semester,csas_form_number,enrolment_no,examination_roll_no,roll_no,admission_year,course_id,admission_criteria\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Admission.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
//added by rahul
        elseif ($page == 'semester') {
            $output = "name,father_name,mother_name,contact_no,parent_contact_no,dob(DD-MM-YYYY),gender_id,category_id,email,current_address,current_state_id,current_pincode,permanent_address,permanent_state_id,permanent_pincode,enrolment_no,roll_no,cuet_score,college_id,course_id,semester,academic_year,admission_year,examination_roll_no\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Intermediate_Semester_Data.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }

        

        elseif ($page == 'student_data') {
            $output = "reference_number, admission_criteria, amount_paid, order_id, transaction_number, board_roll_number, admission_cutoff, course, form_number, college_roll_no, name, enrolment_number, exam_roll_number, email, day, month, year, phone_no, father_guardian_name, father_guardian_occupation, father_guardian_office_address, father_guardian_phone, mothers_name, mothers_occupation, mothers_office_address, mothers_mobile_number, gender, category, apply_to_minority_colleges, is_citizen_of_india, state_of_domicile, university_of_delhi_ward_quota, persons_with_disabilities_category, kashmiri_migrant_category, children_widows_of_armed_forces_personnel, obc_state_name, obc_community, annual_family_income_for_obc, pm_scholarship_jk_students, sikkimese_students_nominated_by_govt_of_sikkim, aadhar_card_no, correspondence_address_line1, correspondence_address_line2, correspondence_city, correspondence_other_city, correspondence_state, correspondence_other_state, correspondence_country, correspondence_other_country, correspondence_pincode, permanent_address_line1, permanent_address_line2, permanent_city, permanent_other_city, permanent_state, permanent_other_state, permanent_country, permanent_other_country, permanent_pincode, board_of_education, yop, result_status, hindi_language_studied_upto, sub1, sub1_p_mm, sub1_obtain_marks, sub1_theory_mm, sub1_theory_obtain, sub1_max_marks, sub1_total_obtain_marks, sub1_normalize_marks, sub1_percentage, sub2, sub2_p_mm, sub2_obtain_marks, sub2_theory_mm, sub2_theory_obtain, sub2_max_marks, sub2_total_obtain_marks, sub2_normalize_marks, sub2_percentage, sub3, sub3_p_mm, sub3_obtain_marks, sub3_theory_mm, sub3_theory_obtain, sub3_max_marks, sub3_total_obtain_marks, sub3_normalize_marks, sub3_percentage, sub4, sub4_p_mm, sub4_total_obtain_marks, sub4_theory_mm, sub4_theory_obtain, sub4_max_marks, sub4_obtain_marks, sub4_normalize_marks, sub4_percentage, sub5, sub5_p_mm, sub5_obtain_marks, sub5_theory_mm, sub5_theory_obtain, sub5_max_marks, sub5_total_obtain_marks, sub5_normalize_marks, sub5_percentage, sub6, sub6_p_mm, sub6_obtain_marks, sub6_theory_mm, sub6_theory_obtain, sub6_max_marks, sub6_total_obtain_marks, sub6_normalize_marks, sub6_percentage, sub7, sub7_p_mm, sub7_obtain_marks, sub7_theory_mm, sub7_theory_obtain, sub7_max_marks, sub7_total_obtain_marks, sub7_normalize_marks, sub7_percentage, type_of_school, last_attended_school_name, last_school_location, medium_of_study, created_by, created_at\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Semester.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        
//added by rahul        
        elseif ($page == 'admission_cancel') {
            $output = "form_no\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Admission Cancel.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
        elseif ($page == 'room') {
            $output = "room_no,description\n";
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=Room.csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo $output;
            exit;
        }
    }
}
