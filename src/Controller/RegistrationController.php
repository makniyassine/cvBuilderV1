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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController

{    private $manager;
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

    #[Route('/register', name: 'app_registration', methods:["POST"])]
    
    public function register(Request $request, ValidatorInterface $validator,SerializerInterface $serializer, TokenGeneratorInterface $tokenGenerator): JsonResponse
    {

        $data=json_decode($request->getContent(),true);
        $email=$data['email'];
        $password=$data['password'];
        $firstName=$data["firstName"];
        $lastName=$data["lastName"] ;
        $tel=$data["tel"];
        $createdAt=new \DateTime();
        $updatedAt=new \DateTime();
        //verifier si l'email existe deja

        $email_exist=$this->user->findOneByEmail($email);
        if($email_exist)
        {
            return new JsonResponse
            (

                [
                    'status'=>false,
                    'message'=>'Cet email exist deja'
                ]
            );
        }
        else
        {
            $user=new User();
            $user->setEmail($email)
                    ->setPassword($this->userPasswordHasher->hashPassword($user,$password))
                    ->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setTel($tel)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($updatedAt);
                    $user->setToken($this->generateToken());

                    // On vérifie les erreurs
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

            $this->manager->persist( $user);
            $this->manager->flush();
            

            $url = $this->generateUrl('confirmAccount', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        // On crée les données du mail
            $context = compact('url', 'user');

            $this->mailer->sendEmail($user->getEmail(), $url);

            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'user cree avec success'
                ]
            );
        }
    }


    
    #[Route('/confirmAccount/{token}', name: 'confirmAccount', methods:["POST"])]

    public function confirmAccount(string $token)
    {
        $user = $this->user->findOneBy(["token" => $token]);
        if($user) {
            $user->setToken(null);
            $user->setEnabled(true);
            $this->manager->persist( $user);
            $this->manager->flush();
            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'user confirmed avec success'
                ]
            );
            
        } else {
            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'user non confirmed '
                ]
            );

        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }


}

