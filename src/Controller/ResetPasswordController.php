<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    private $manager;
    private $user;
    private $mailer;

    private $userPasswordHasher;
    public function __construct(EntityManagerInterface $manager ,Mailer $mailer, UserRepository $user, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->manager=$manager;
        $this->user=$user;

        $this->mailer = $mailer;

        $this->userPasswordHasher = $userPasswordHasher;
    }

    //Récupérer mot de passe oublié
#[Route('/reset-password', name: 'forgotten_password'  , methods:["POST"])]
public function forgottenPassword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, Mailer $mailer): Response
{
    // Récupérer les données JSON de la requête
    $data = json_decode($request->getContent(), true);

    // Vérifier si les données JSON sont valides
    if (!isset($data['email'])) {
        return new JsonResponse(['message' => 'Email is required'], Response::HTTP_BAD_REQUEST);
    }

    // On va chercher l'utilisateur par son email
    $user = $userRepository->findOneByEmail($data['email']);

    // On vérifie si on a un utilisateur
    if ($user) {
        // Générer un timestamp pour l'expiration du token (par exemple, 24 heures à partir de maintenant)
        $expirationTimestamp = strtotime('+0,0333333 hours'); //120 secondes

        // On génère un token de réinitialisation
        $token = $tokenGenerator->generateToken();

        // Ajouter le token et l'expiration à l'utilisateur
        $user->setToken($token);
        $user->setTokenDate(new \DateTime('@' . $expirationTimestamp));

        $entityManager->persist($user);
        $entityManager->flush();

        // On génère un lien de réinitialisation du mot de passe
        $url = $this->generateUrl('reset-password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        // On crée les données du mail
        $context = compact('url', 'user');

        // Envoi du mail
        $mailer->sendEmail($user->getEmail(), $url);

        return new JsonResponse(['message' => 'Email envoyé avec succès'], Response::HTTP_OK);
    }

    // $user est null
    return new JsonResponse(['message' => 'Adresse email invalide'], Response::HTTP_BAD_REQUEST);
}
#[Route('/reset-password/{token}', name: 'reset-password')]
public function resetPass(string $token, Request $request, UserRepository $usersRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
{
    // On vérifie si on a ce token dans la base
    $user = $usersRepository->findOneBy(['token' => $token]);

    // Vérifier si le token est valide
    if ($user && $user->getTokenDate() > new \DateTime()) {
        // Réinitialiser le mot de passe de l'utilisateur
        // Votre logique de réinitialisation de mot de passe ici

        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['password'])) {
            return new JsonResponse(['message' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }

        $user->setToken(null);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $requestData['password']));

        $entityManager->flush();

        return new JsonResponse(['message' => 'Mot de passe changé avec succès'], Response::HTTP_OK);
    }

    // Le token est invalide
    return new JsonResponse(['message' => 'Jeton invalide'], Response::HTTP_BAD_REQUEST);
}
}