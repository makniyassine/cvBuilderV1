<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class Mailer {
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail($email, $token)
    {
        $message = (new Email())
            ->from('yassinemakni@fss.u-sfax.tn')
            ->to($email)
            ->subject('Thanks for signing up!')
            ->text('Here is your token: ' . $token);

        $this->mailer->send($message);
    }
}