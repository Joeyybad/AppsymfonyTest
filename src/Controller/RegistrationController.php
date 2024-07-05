<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Service\MailerService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
     * @Route("/register", name="app_register", methods={"GET"})
     */
    public function showRegistrationForm(): Response
    {
        $form = $this->createForm(RegistrationFormType::class);

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register", name="app_register_process", methods={"POST"})
     */
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        MailerService $mailerService,
        TokenGeneratorInterface $tokenGeneratorInterface
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TOKEN
            $tokenRegistration = $tokenGeneratorInterface->generateToken();

            // USER
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setCreatedAt(new DateTime());

            // USER TOKEN
            $user->setTokenRegistration($tokenRegistration);
            
            $entityManager->persist($user);
            $entityManager->flush();

            // MAILER
            $mailerService->send(
                $user->getEmail(),
                'Confirmation compte utilisateur',
                'registration_confirmation.html.twig',
                [
                    'user' => $user,
                    'token' => $tokenRegistration,
                    'lifeTimeToken' => $user->getTokenRegistrationLifeTime()->format('d-m-Y-H-i-s')
                ]
            );
            $this->addFlash('success', 'Votre compte a bien été créé, veuillez vérifier votre email pour l\'activer');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    
    /**
     * @Route("/verify/{token}", name="app_verify")
     */
    
    public function verify(string $token, EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry): Response
    {
        // Rechercher l'utilisateur par le token
        $user = $entityManager->getRepository(User::class)->findOneBy(['tokenRegistration' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('No user found for this token.');
        }

        // Vérifier si le token est expiré
        if (new \DateTime('now') > $user->getTokenRegistrationLifeTime()) {
            throw new AccessDeniedException('This token is expired.');
        }

        // Activer l'utilisateur
        $user->setIsVerified(true);
        $user->setTokenRegistration(null); // Supprimer le token après vérification

        // Utiliser le manager approprié pour persister les modifications
        $em = $managerRegistry->getManagerForClass(get_class($user));
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a bien été créé, vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }
}
