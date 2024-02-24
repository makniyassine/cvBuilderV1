<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private $manager;
    private $user;
    public function __construct(EntityManagerInterface $manager , UserRepository $user)
    {
        $this->manager=$manager;

        $this->user=$user;
        
    }

    // creation d'un utilisateur
    #[Route('/userCreate', name: 'app_user', methods:'POST')]
    public function userCreate(Request $request): JsonResponse
    {

        $data=json_decode($request->getContent(),true);
        $email=$data['email'];
        $password=$data['password'];
        $firstName=$data["firstName"];
        $lastName=$data["lastName"] ;
        $tel=$data["tel"];
        $enable=$data["enabled"];
        $blocked=$data["blocked"];
        $createdAt=new \DateTimeImmutable();
        $updatedAt=new \DateTimeImmutable();
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
                    ->setPassword(sha1($password))
                    ->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setTel($tel)
                    ->setEnabled($enable)
                    ->setBlocked($blocked)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($updatedAt);

            $this->manager->persist( $user);
            $this->manager->flush();

            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'user cree avec success'
                ]
            );
        }
    }

     // liste des utilisateur
    #[Route('/api/getAllUsers', name: 'get_allusers', methods:'GET')]
    public function GetAllUser(Request $request,SerializerInterface $serializer): JsonResponse
    {

        $users=$this->user->findAll();
        $jsonUsersList = $serializer->serialize($users, 'json');
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }
}
