<?php

namespace App\Console\Commands;

use App\Mail\EmailSimples;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class playground extends Command
{
    protected $signature = 'email:enviar {para}';
    protected $description = 'Envia um e-mail simples';

    public function handle()
    {
        $para = $this->argument('para');
        Mail::to('leandro@uex.io')->send(new EmailSimples('Olá! Este é um e-mail simples enviado pelo Laravel.'));
        $this->info("E-mail enviado para {$para}.");
    }
}
