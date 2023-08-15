<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Service\MailerService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\Query\AST\Functions\CurrentTimeFunction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as ExceptionAccessDeniedException;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
        TokenGeneratorInterface $tokenGeneratorInterface): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
            
            //TOKEN
            $tokenRegistration = $tokenGeneratorInterface->generateToken();

            //USER
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
            )
                
            );
            $user->setCreatedAt(new DateTime());

            //USERTOKEN 
            $user->setTokenRegistration($tokenRegistration);
            
            $entityManager->persist($user);
            $entityManager->flush();

            // MAILER 
            $mailerService->send(
                $user->getEmail(),
                'Confirmation compte utilisateur',
                'registration_confirmation.html.twig',
                [
                    'user'=> $user,
                    'token' => $tokenRegistration,
                    'lifeTimeToken' => $user->getTokenRegistrationLifeTime()->format('d-m-Y-H-i-s')

                ]
                );
            $this->addFlash('success','Votre compte a bien été crée, veuillez verifier votre email pour l\'activer'); 

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    
    /**
     * @Route("/verify/{token}/{id<\d+>}", name="account_verify", methods={"GET"})
     */
    
   
    public function verify(string $token, User $user, EntityManagerInterface $em, ManagerRegistry $managerRegistry): response{
        if($user->getTokenRegistration() !== $token){
            throw new ExceptionAccessDeniedException();
        }
        if ($user->getTokenRegistration() === null){
            throw new ExceptionAccessDeniedException();
        }
        if(new DateTime('now') > $user->getTokenRegistrationLifeTime()) {
        throw new ExceptionAccessDeniedException();
        }
        $em = $managerRegistry->getManagerForClass(get_class($user));
        $em->persist($user);
        $user->setIsVerified(true);
        $user->setTokenRegistration(null);
        $em->flush();
        $this->addFlash('success', 'Votre compte à bien été crée, vous pouvez maintenant vous connecter');

        return $this->redirectToRoute('app_login');
    }
}
