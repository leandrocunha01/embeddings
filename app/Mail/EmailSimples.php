<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailSimples extends Mailable
{
    use Queueable, SerializesModels;

    public $mensagem;

    public function __construct($mensagem)
    {
        $this->mensagem = $mensagem;
    }

    public function build()
    {
        return $this->subject('Assunto Teste')
            ->view('emails.simples');
    }
}

