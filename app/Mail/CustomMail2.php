<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomMail2 extends Mailable
{
     use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $subject;
    public $content;

    public function __construct($subject, $content, $blade_file)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->blade_file = $blade_file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view($this->blade_file, ['content' => $this->content]);
    }
}
