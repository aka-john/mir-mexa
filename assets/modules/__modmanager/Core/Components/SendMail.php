<?php

namespace Core\Components;

/*
 * Mail sender class
 */
class SendMail {
    private $modx;
    private $mailer;

    public function __construct(){
        global $modx;
        $this->modx = $modx;
    }


    public function send($to, $subject, $body, $file = null)
    {
        $this->modx->loadExtension('MODxMailer');
        $this->modx->mail->Subject = $subject;
        $this->modx->mail->AddAddress($to);
        $this->modx->mail->MsgHTML($body);
        $this->modx->mail->Send();
    }
}
