<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Transport\TransportInterface;

class MailerService
{
    private $mailer;

    public function __construct(TransportInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to): void
    {
        $email = (new Email())
            ->from(new Address('cloudme2023@gmail.com', 'Propar'))
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Propar: Votre nettoyage est terminé !')
            ->html("<p>Bonjour<br><br>
            Votre facture est disponible ci-joint !<br><br>
            Propar vous remercie pour votre confiance !</p>")
            //renseignez votre chemin ou ce situe le fichier facture.pdf dans le dossier public/pdf/...
            //   ->attachFromPath('Votre chemin ici !!');
            ->attachFromPath('C:\Users\DeLL\Desktop\proparmohir\PROPAR\public\pdf\facture.pdf');
        $this->mailer->send($email);
    }
}