<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private $manager;
    private $user;
    private $userPasswordHasher;
    public function __construct(EntityManagerInterface $manager , UserRepository $user, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->manager=$manager;

        $this->user=$user;

        $this->userPasswordHasher = $userPasswordHasher;
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

    #[Route('/api/Users/{firstName}', name: 'getuser', methods: ['GET'])]
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUsers = $serializer->serialize($user, 'json', ['groups'=> "getUsers"]);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }


    #[Route('/api/Users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        if (!$em->contains($user)) {
            return new JsonResponse(['status' => false, 'message' => 'Utilisateur déjà supprimé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['status' => true, 'message' => 'Utilisateur supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }

    /// metre a jour user

    #[Route('/api/Users/{id}', name:"updateUser", methods:['PUT'])]
    public function updateUser(Request $request, $id, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->user->find($id);

        if (!$user) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Utilisateur non trouvé'
            ],
            Response::HTTP_NOT_FOUND
        );
    }

        $user->setFirstName($data['firstName'] ?? $user->getFirstName())
            ->setLastName($data['lastName'] ?? $user->getLastName())
            ->setTel($data['tel'] ?? $user->getTel());

    $em->persist($user);
    $em->flush();

    return new JsonResponse(
        [
            'status' => true,
            'message' => 'Utilisateur mis à jour avec succès'
        ]
    );
}

     // liste des utilisateur
    #[Route('/api/getAllUsers', name: 'get_allusers', methods:'GET')]
    public function GetAllUser(Request $request,SerializerInterface $serializer): JsonResponse
    {

        $users=$this->user->findAll();
        $jsonUsersList = $serializer->serialize($users, 'json', ['groups'=> "getUsers"]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }
}
