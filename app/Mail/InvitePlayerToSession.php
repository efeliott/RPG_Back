<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Session;


class InvitePlayerToSession extends Mailable
{
    use Queueable, SerializesModels;

    public $session;
    public $link;

    public function __construct(Session $session, $link)
    {
        $this->session = $session;
        $this->link = $link;
    }

    public function build()
    {
        return $this->view('emails.invite')
                    ->with([
                        'sessionName' => $this->session->name,
                        'link' => $this->link,
                    ]);
    }
}
