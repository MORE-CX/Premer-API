<?php
namespace App\Lib;

use Firebase\JWT\JWT,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer,
    Nette\Mail\SmtpMailer;


class Email
{
    private static $email = "*****@gmail.com";
    private static $password = "*****";
    private static $url = "https://premersite.000webhostapp.com/public/auth";//"http://localhost/Premer/api/public/auth";
    private static $secret_key = "Tomate11@";

    public function sendEmailActivacion($userEmail)
    {
        $md5 = md5($userEmail.date('Ymdhis'));
        $token = array(
            'email' => $userEmail,
            'activ_code' => $md5
        );
        $activCode = JWT::encode($token, self::$secret_key);
        $mail = new Message;
        $mailer = new SmtpMailer([
            'host' => 'smtp.gmail.com',
            'username' => self::$email,
            'password' => self::$password,
            'secure' => 'tls',
        ]);
        $mail->setFrom('Premer <' . self::$email . '>')
            ->addTo($userEmail)
            ->setSubject('Email de confirmacion')
            ->setHTMLBody("Hola, para confirmar tu cuenta, as click en este enlace: <br />
            <a target='_blank' href='" . self::$url . "/activate/" . $activCode . "'>
            Activar Cuenta</a>");
        $mailer->send($mail);
        return $md5;
    }

    public function sendEmailDefinirPassword($userEmail)
    {
        $mail = new Message;
        $mailer = new SmtpMailer([
            'host' => 'smtp.gmail.com',
            'username' => self::$email,
            'password' => self::$password,
            'secure' => 'tls',
        ]);
        $newPass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10)."HH22";
        $mail->setFrom('Premer <' . self::$email . '>')
            ->addTo($userEmail)
            ->setSubject('Email de definicion de password')
            ->setHTMLBody("Hola!. Aun debes definir un password para tu cuenta. <br />
            Puedes utilizar este hasta que lo actualices: <br/>
            <big><b>" . $newPass . "</b></big>
            ");
        $mailer->send($mail);
        return md5(md5($newPass));
    }

}
