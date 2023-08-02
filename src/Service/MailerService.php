<?php 
namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Service qui permet de générer un mail
 */
class MailerService{

   private MailerInterface $mailerInterface;

   public function __construct( MailerInterface $mailerInterface)
   { 
    $this->mailerInterface = $mailerInterface;
   }

   public function send(
    string $to,
    string $subject,
    string $templateTwig,
    array $context):void
   {
    $email = (new TemplatedEmail())
    ->from(new Address('noreply@monsitesneaker.fr', 'Monsitesneaker'))
    ->to($to)
    ->subject($subject)
    ->htmlTemplate("mails/$templateTwig")
    ->context($context);

    try {
        $this->mailerInterface->send($email);
    } catch(TransportException $transportException) {
        throw $transportException; 
    }

}
   
   
}