<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Mail\CustomMail;
use App\Mail\CustomMail2;
use App\Mail\PortalMail;

use Mail;
use DB;
use Session;
use Crypt;
use Auth;

use App\Models\College;
use App\Models\Department;
use App\Models\PG;
use App\Models\Faculty;
use App\Models\User_Profile;
use App\Models\Mailer;


class MailController extends Controller {

     public function __construct() {
        $this->current_menu = 'Mailer';
    }
    public function update(Request $request) {      //used for send email
        // dd($request);
        // $faculty_id_arr = !empty($request->to)?$request->to:[];
        // // dd($faculty_id_arr);
        
        // if(count($faculty_id_arr)>0) {
        //     $to_email_arr = DB::table('faculty_mast')
        //                         ->whereIn('id', $faculty_id_arr)
        //                         ->where('email_id', '!=', NULL)
        //                         ->pluck('email_id');
        // // dd($to_email_arr);

        // }

        // else {
        //     dd('No Recipient Selected');
        // }
        // dd($to_email_arr);
        // $final_to_email_arr  = [];
        // foreach($to_email_arr as $key => $value) {
        //     $final_to_email_arr[] = str_replace(' ', '', $value);
        // }   

        // dd($final_to_email_arr);

        //////mail to all via bcc starts here 

        // $students_arr = DB::table('user_profile')
        //                 ->where('semester', 1)
        //                 ->get();

        // $final_arr = [];
        // foreach($students_arr as $key => $value) {
        //     $final_arr[$value->course_id][] = $value->email;
        // }

        dd($final_arr);

  $arr_cus_def = [
          "aarnakatyal12@gmail.com",
          "Swaggeraarti@gmail.com",
          "ada.agarwal28@gmail.com",
          "addyakaur3121@gmail.com",
          "aditishukladevesh@gmail.com",
          "aishwaryachawla2004@gmail.com",
          "akritigre8@gmail.com",
          "akshikatomar22@gmail.com",
          "04akshita.sharma@gmail.com",
          "akshitakushwaha2005@gmail.com",
          "alexiyaminj@gmail.com",
          "chauhanas2005@gmail.com",
          "rawatanamika648@gmail.com",
          "anchalmishra5g@gmail.com",
          "anjalishekhar746@gmail.com",
          "Samotaanjana4@gmail.com",
          "30.xb.annu@gmail.com",
          "chaudharyanshika0101@gmail.com",
          "bkranjan52@gmail.com",
          "radhavansi1982@gmail.com",
          "arpita25800@gmail.com",
          "arpita.saini2004@gmail.com",
          "psgaang2021@gmail.com",
          "arshiajain99@gmail.com",
          "srivastavaakash916@gmail.com",
          "asthamallick46@gmail.com",
          "awanimaheshwari24@gmail.com",
          "pandithimanshu649@gmail.com",
          "bhavya190705@gmail.com",
          "kpappi61@gmail.com",
          "sharmabhumi1157@gmail.com",
          "bushrat7890@gmail.com",
          "chahakkoolwal@gmail.com",
          "charuchohan1@gmail.com",
          "cv.chetanyaverma@gmail.com",
          "dchaturvedi0250@gmail.com",
          "deepanshisagwan7@gmail.com",
          "dhwanijindal2806@gmail.com",
          "dikshakapur08@gmail.com",
          "dushyantvashisth532@gmail.com",
          "divya10march2005@gmail.com",
          "divyanshisharma5737@gmail.com",
          "Nepalsinghgurjar956@gmail.com",
          "drishti83039@gmail.com",
          "drishtiaswal@gmail.com",
          "drishti3712@gmail.com",
          "dolkeredzes@gmail.com",
          "garimasehrawat2006@gmail.com",
          "gs1234965@gmail.com",
          "garvitagogia20@gmail.com",
          "garvita.saini2005@gmail.com",
          "chauhangauri1002@gmail.com",
          "gaurisinghh23@gmail.com",
          "geenishkakhanna@gmail.com",
          "goonghosh1229@gmail.com",
          "simranverma2099@gmail.com",
          "gunjakumari16626@gmail.com",
          "my.fav30@gmail.com",
          "rs134gs@gmail.com",
          "Jangirsarita769@gmail.com",
          "sudhanshu170998@gmail.com",
          "hibaansari929@gmail.com",
          "dagarhimanshi940@gmail.com",
          "himanshi.3328@gmail.com",
          "wajhoor@rediffmail.com",
          "indrakshikansal113@gmail.com",
          "rekhamathur9311@gmail.com",
          "dreamerisha5@gmail.com",
          "soodisha251126@gmail.com",
          "saritasharma37515@gmail.com",
          "ishitathakral17@gmail.com",
          "SINGHNAYARA5689@GMAIL.COM",
          "snehajinnaram761@gmail.com",
          "vaibhaw006@gmail.com",
          "kajalkumari610stylist@gmail.com",
          "sandhyaranibeherask@gmail.com",
          "skhurana2576@gmail.com",
          "kanishkabhatia19@gmail.com",
          "karinakumari0494@gmail.com",
          "nitikakhurana24@gmail.com",
          "kashishtanwar261204@gmail.com",
          "kashvimittal295@gmail.com",
          "khangembamlakshmibai@gmail.com",
          "kaloriyasoniya@gmail.com",
          "khushibazard1728@gmail.com",
          "dhankarpriya8@gmail.com",
          "cakamlesh2010@gmail.com",
          "khwaishkhandelwal21@gmail.com",
          "kiranlodwal5@gmail.com",
          "goswamikirti896@gmail.com",
          "kirtilakhera37@gmail.com",
          "chauhankirtivardhan06@gmail.com",
          "vigneshraj946@gmail.com",
          "kratimahajan180@gmail.com",
          "ramolasanjana2@gmail.com",
          "fashionhub088@gmail.com",
          "laxmirauniyar77@gmail.com",
          "anuragk8851@gmail.com",
          "vaishnaviwows2@gmail.com",
          "vyaallovely@gmail.com",
          "Surenrupal80@gmail.com",
          "mahakverma687@gmail.com",
          "prakriti0903@gmail.com",
          "mukeshkumar.mk69@gmail.com",
          "mgmehakgupta2006@gmail.com",
          "mehakk1678@icloud.com",
          "misthishanker2312@gmail.com",
          "swamikavita030@gmail.com",
          "muskangaur655@gmail.com",
          "chauhandimple02121974@gmail.com",
          "jatinsinghal123456@gmail.com",
          "muskan.talwar1111@gmail.com",
          "naazbano886@gmail.com",
          "nandanisharmma1@gmail.com",
          "harshftp7@gmail.com",
          "navyaagarwal1911@gmail.com",
          "pathaknavya13@gmail.com",
          "neelambhadri254@gmail.com",
          "barjun1843@gmail.com",
          "nehajiaswal6@gmail.com",
          "komalcraneservice2311@gmail.com",
          "nidhineharishabh@gmail.com",
          "nidhisinghbhu1@gmail.com",
          "Officialnidhi1909@gmail.com",
          "niharikasingh1129@gmail.com",
          "niharika2415@gmail.com",
          "nikitakhatana13@gmail.com",
          "navendu.it@gmail.com",
          "palkityagi541@gmail.com",
          "pallavik3379@gmail.com",
          "pearl.nagpal26@gmail.com",
          "poojadalakoti8@gmail.com",
          "prachisharma4752@gmail.com",
          "ps093648@gmail.com",
          "prachiyadav2409@gmail.com",
          "mukeshjainkumar1975@gmail.com",
          "27pragyavats@gmail.com",
          "pragyayadav958@gmail.com",
          "swaroopprashansa@gmail.com",
          "kpal3954@gmail.com",
          "Prernachauhan931@gmail.com",
          "prishaseth0303@gmail.com",
          "prishatewari.07@gmail.com",
          "0075prityrai@gmail.com",
          "sharda9199723902@gmail.com",
          "rvaishali509@gmail.com",
          "PJ926061@GMAIL.COM",
          "rachitajiya2003@gmail.com",
          "singhanjali72002@gmail.com",
          "nandani024sharma@gmail.com",
          "nareshroyji123@gmail.com",
          "rani231822@gmail.com",
          "rashiarora1710@gmail.com",
          "pujamohankumar@gmail.com",
          "ridhikaarora11d@gmail.com",
          "veerendrk289@gmail.com",
          "jainmithlesh1008@gmail.com",
          "ritabhyasingh0@gmail.com",
          "ritika.s9910@gmail.com",
          "TRIPATHI.RITIKA1608@GMAIL.COM",
          "Kritu6488@gmail.com",
          "danveersolanki20@gmail.com",
          "Riyav72250@gmail.com",
          "rozyr2399@gmail.com",
          "S95067964@GMAIL.COM",
          "saheliparia26@gmail.com",
          "shivamjjn333001@gmail.com",
          "sunilomdubey03@gmail.com",
          "laxmi01jan87@gmail.com",
          "choprasamaira76@gmail.com",
          "daslalck@gmail.com",
          "sanchitarathore2004@gmail.com",
          "manishaaa037@gmail.com",
          "KAMLESHYADAV96431@GMAIL.COM",
          "sarahrajgaur003@gmail.com",
          "pandeysaumya1906@gmail.com",
          "tejp1640@gmail.com",
          "shanvisingh991@gmail.com",
          "kumarishaashi@gmail.com",
          "ashuyadav92687@gmail.com",
          "yadavshaumya2004@gmail.com",
          "singhnarenderpal1969@gmail.com",
          "ylrehs2367@gmail.com",
          "shireenbahar7@gmail.com",
          "somyasingh46258@gmail.com",
          "Shivanyapriyadarshani@gmail.com",
          "shraddhasinha7777@gmail.com",
          "shreyaofficial0225@gmail.com",
          "shreyasingh80900@gmail.com",
          "anilkumar28217@gmail.com",
          "shrishti2723@gmail.com",
          "yeashshukla9910@gmail.com",
          "shristiujinwal26092004@gmail.com",
          "shwetabharat1033@gmail.com",
          "mailshweta2004@gmail.com",
          "shadijasimridhi@gmail.com",
          "siyapasricha@gmail.com",
          "sneha20180133734@gmail.com",
          "snehakumari1341@gmail.com",
          "Shivikasingh588@gmail.com",
          "sonalsharrmaa2020@gmail.com",
          "satywantokes@gmail.com",
          "rschoudhry04@gmail.com",
          "shahisoumya15@gmail.com",
          "soundarya21000@gmail.com",
          "srashti886@gmail.com",
          "shrutiikashyap16@gmail.com",
          "amangupta93057@gmail.com",
          "sugandhamudgal02@gmail.com",
          "shuklajiknp@gmail.com",
          "thesuhani2005@gmail.com",
          "sumandas220044@gmail.com",
          "sumankrii123@gmail.com",
          "NAVEEN.2906@GMAIL.COM",
          "Swastirawat288@gmail.com",
          "tk1792177@gmail.com",
          "tanisha43633@gmail.com",
          "tanisharaghav3@gmail.com",
          "tanishkabaweja@gmail.com",
          "devraj1577@gmail.com",
          "TANU0PANDEY@GMAIL.COM",
          "tanyagupta.jasmine@gmail.com",
          "Poonamkashyap2125@gmail.com",
          "tanyanishad185@gmail.com",
          "vs46618@gmail.com",
          "teena9326@gmail.com",
          "titikshatitiksha8@gmail.com",
          "poonampoonam34536@gmail.com",
          "chorolt234@gmail.com",
          "shivkumar94469@gmail.com",
          "gurshimrankaurmann@gmail.com",
          "unnatigupta6908@gmail.com",
          "kanchanprinter2001@gmail.com",
          "pandeyvaishnavi794@gmail.com",
          "ravinayadav9785@gmail.com",
          "rajndmcdelhi@gmail.com",
          "sumanbai8584@gmail.com",
          "vanshikagujral9@gmail.com",
          "vanshikajain15112005@gmail.com",
          "naman8657@gmail.com",
          "varalikalavania@gmail.com",
          "Varshabaisoya89@gmail.com",
          "vidhidas836@gmail.com",
          "vrindatuli39@gmail.com",
          "yaminidhingan781@gmail.com",
          "bhatiyashasvi12@gmail.com",
          "yashiprabha5993@gmail.com",
          "bhagatrajguru23@gmail.com",
          "zeenathayat42@gmail.com"
        ];
        // dd($arr_cus_def);
        // $arr_cus_def = ["abc@gmail.com"];

        $subject = 'Selection of SEC, VAC, AEC, and GE Papers for U. G. 1st Semester Students';
        $content = "123";

        // foreach ($arr_cus_def as $key => $bcc_email_arr) {
            // Send email using the FacultyEmail Mailable
            // $response = Mail::to($value)
            // $bcc_email_arr[] = 'info.puneetkverma@gmail.com';
            // dd($bcc_email_arr);
        // }
        // $bcc_email_arr = $arr_cus_def;
            $response = Mail::bcc($bcc_email_arr)
                            ->send(new CustomMail($subject, $content));           
        dd('done');



        /////mail to all via bcc ends here





        /*
        /////mail 1 sent individually to all starts here 
        $students_arr = DB::table('user_profile')
                            ->where('semester', 1)
                            ->where('course_id', 1)
                            ->pluck('email', 'id');

        dd($students_arr);



        $to_email_arr[] = 'vaibhavaggarwal.124@gmail.com';
        $bcc_email_arr[] = 'info.puneetkverma@gmail.com';
        $bcc_email_arr[] = 'abc@gmail.com';

        $subject = 'Selection of SEC, VAC, AEC, and GE Papers for U. G. 1st Semester Students';
        $content = "123";

        // dd($to_email_arr, $subject, $content);
        $msg_sent_to = [];
        // $msg_sent_to_id = [];

        $log_arr = [];
        $final_arr_to_store = [];

        foreach ($to_email_arr as $key => $value) {
            // Send email using the FacultyEmail Mailable
            // $response = Mail::to($value)
            $response = Mail::bcc($bcc_email_arr)
                            ->send(new CustomMail($subject, $content));
            $msg_sent_to[] = $value;
            $log_arr = [
                'user_profile_id' => $key,
                'user_profile_email' => $value,
                'sent_by' => Auth::user()->id,
                'server_datetime' => date('Y-m-d H:i:s'),
            ];
            $final_arr_to_store[] = $log_arr;            
        }
        if(count($final_arr_to_store)>0) {
            DB::table('mail_logs')->insert($final_arr_to_store);
        }
        dd('done', $response, count($msg_sent_to), $msg_sent_to);
        return response()->json(['message' => 'Emails sent successfully']);
        /////mail 1 sent individually to all ends here 
    */


    }
    
   
    public function index() {
        // dd(1);
        $role_id = Auth::user()->role_id;
        if($role_id == 4) {   //faculty
            $users_id = Auth::user()->id;
            $user_profile_data = User_Profile::getDataFromUsersId($users_id);
            if(empty($user_profile_data)) {
                dd('User Profile not created for the logged in user');
            }
            // dd($users_id, $user_profile_data);
            $college_to_select = $user_profile_data->college_id;
            $college_arr = College::pluckActiveCodeAndName($college_to_select);
        }
        else {
            $college_arr = College::pluckActiveCodeAndName();
        // dd($college_arr);
        }
        // dd($college_arr);
        // $all_data=DB::table('CkEditor')->pluck('body','id');

           // dd($all_data);
        
        return view($this->current_menu.'/index', [
            'college_arr' => $college_arr,
            // 'data'=>$all_data,
            'current_menu' => $this->current_menu,
        ]);
    }

    public function ckeditor_index() {
        // dd(1);
        $role_id = Auth::user()->role_id;
        if($role_id == 4) {   //faculty
            $users_id = Auth::user()->id;
            $user_profile_data = User_Profile::getDataFromUsersId($users_id);
            if(empty($user_profile_data)) {
                dd('User Profile not created for the logged in user');
            }
            // dd($users_id, $user_profile_data);
            $college_to_select = $user_profile_data->college_id;
            $college_arr = College::pluckActiveCodeAndName($college_to_select);
        }
        else {
            $college_arr = College::pluckActiveCodeAndName();
        // dd($college_arr);
        }
        // dd($college_arr);
        $all_data=DB::table('CkEditor')->pluck('body','id');

           // dd($all_data);
        
        return view($this->current_menu.'/format_mail', [
            'college_arr' => $college_arr,
            'data'=>$all_data,
            'current_menu' => $this->current_menu,
        ]);
    }

    public function send_mail_to_students(Request $request) {
        // code...
        //this is for students
        // dd(1, $request);
        $college_id = !empty($request->college_id)?$request->college_id:NULL;
        if($college_id == NULL) {
            dd('College Id NOt Passed');
        } 
        $course_arr = !empty($request->course_arr)?$request->course_arr:[];
        $semester_arr = !empty($request->semester_arr)?$request->semester_arr:[];
        $recipients_arr = !empty($request->recipients_arr)?$request->recipients_arr:[];
        $subject = !empty($request->subject)?$request->subject:'';
        $body_arr = !empty($request->body_arr)?$request->body_arr:[];
        $content = $body_arr;
        $students_arr = DB::table('user_profile')
                            ->where('semester', '!=', NULL)
                            ->where('status',1);

        if(count($course_arr)>0) {
            $students_arr->whereIn('course_id', $course_arr);
        }

        if(count($semester_arr)>0) {
            $students_arr->whereIn('semester', $semester_arr);
        }

        if(count($recipients_arr)>0) {
            $students_arr->whereIn('id', $recipients_arr);
        }

        $final_student_arr = $students_arr->get();

        // dd($final_student_arr);

        $final_arr = [];
        foreach($final_student_arr as $key => $value) {
            $final_arr[$value->course_id][] = $value->email;
        }
        // $final_arr = [];
        // $final_arr[1][] = 'info.puneetkverma@gmail.com';
        // $final_arr[0][] = 'ndtf2023@gmail.com';
        // $final_arr[0][] = 'abc@gmail.com';

        // dd($final_arr);
        $auth_data = Auth::user();
        $college_id = !empty($auth_data)?$auth_data->college_id:NULL;
        $company_id = !empty($auth_data)?$auth_data->company_id:NULL;
        
        foreach ($final_arr as $key => $bcc_email_arr) {
            //set credentials
            $set_credentials = Mailer::set_credentials($college_id, $company_id);
            $response = Mail::bcc($bcc_email_arr)
                            ->send(new PortalMail($subject, $content));           
        }
        $message = 'Mails Sent Successfully';
        Session::flash('message', $message);
        return redirect('Mailer'); 
        // dd('hogya');
    }


    public function forgot_password_mail($mail_id, $token, $college, $company) {
        // dd($mail_id, 1); 
        $user_email_id = $mail_id;
        $subject = 'Forgot Password Request';
        $content = "A Request is initiated for reseting your password. Please use ".$token." as your security token to proceed.";
        // $user_email_id = 'abc@gmail.com';
        $blade_file = 'emails.forgot_password';

        //set credentials
        // $auth_data = Auth::user();
        $college_id = !empty($college)?$college:NULL;
        $company_id = !empty($company)?$company:NULL;
        $set_credentials = Mailer::set_credentials($college_id, $company_id);
        
        $response = Mail::to($user_email_id)
                            ->send(new CustomMail2($subject, $content, $blade_file));
        return true;
    }


    public function test_mail() {
        // dd('test');
        $user_email_id = 'vbhagg@gmail.com';
        $subject = 'Test Mail';
        $content = "This mail is generated for testing purposes. Please ignore.";
        // $user_email_id = 'abc@gmail.com';
        $blade_file = 'emails.forgot_password';
        // $response = Mail::send(new CustomMail2($subject, $content, $blade_file), array(1,23),function($message) use($user_email_id,$subject)
        //                     {
                                    
        //                         $message->from('gmayank686@gmail.com');

        //                         $message->to($user_email_id)->subject($subject);

        //                     });


        //set credentials
        $auth_data = Auth::user();
        $college_id = !empty($auth_data)?$auth_data->college_id:NULL;
        $company_id = !empty($auth_data)?$auth_data->company_id:NULL;
        $set_credentials = Mailer::set_credentials($college_id, $company_id);

        // dd($auth_data);

        $response = Mail::to($user_email_id)
                            ->send(new CustomMail2($subject, $content, $blade_file));
        return true;   
    }
}
