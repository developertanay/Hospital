<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DB;

class Mailer extends Model
{
    // use HasFactory;

    public static function set_credentials($college_id, $company_id) {
        if(!empty($college_id)) {
            $mail_id = DB::table('mailer_credentials')
                    ->where('college_id', $college_id)
                    ->first();    
        }
        else if(!empty($company_id)) {
            $mail_id = DB::table('mailer_credentials')
                    ->where('company_id', $company_id)
                    ->where('status',1)
                    ->first();
        }
        else {
            $mail_id = DB::table('mailer_credentials')
                    ->where('college_id', NULL)
                    ->where('company_id', NULL)
                    ->where('status',1)
                    ->first();    
        }

        if(!empty($mail_id)) {
            $email = !empty($mail_id->email_id)?$mail_id->email_id:NULL;
            $password = !empty($mail_id->password)?$mail_id->password:NULL;
            $name = !empty($mail_id->username)?$mail_id->username:'Administrator';

            if(!empty($email) && !empty($password)) {
                config([
                   'mail.mailers.smtp.username' => $email,
                   'mail.mailers.smtp.password' => $password,
                   'mail.from.address' => $email,
                   'mail.from.name' => $name
               ]);
            }
            else {

            }
        }
        else {

        }
        return 1;
    }


}
