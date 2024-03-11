<?php

namespace App\Controller;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PersonController extends AbstractController
{
    private $manager;
    private $person;
    public function __construct(EntityManagerInterface $manager , PersonRepository $person, )
    {
        $this->manager=$manager;

        $this->person=$person;

    }
    
    
    

    // creation d'un utilisateur
    #[Route('/personCreate', name: 'creatperson', methods:'POST')]
    public function personcreate(Request $request): JsonResponse
    {

        $data=json_decode($request->getContent(),true);
        $email=$data['email'];
        $firstName=$data["firstName"];
        $lastName=$data["lastName"] ;
        $createdAt=new \DateTimeImmutable();
        $updatedAt=new \DateTimeImmutable();
        //verifier si l'email existe deja

        $email_exist=$this->person->findOneByEmail($email);
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
            $person=new Person();
            $person->setEmail($email)
                    ->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($updatedAt);

            $this->manager->persist( $person);
            $this->manager->flush();

            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'person cree avec success'
                ]
            );
        }
    }

    #[Route('/api/Person/{firstName}', name: 'getperson', methods: ['GET'])]
    public function getDetailperson(Person $person, SerializerInterface $serializer): JsonResponse
    {
        $jsonPersons = $serializer->serialize($person, 'json', ['groups'=> "getPersons"]);
        return new JsonResponse($jsonPersons, Response::HTTP_OK, [], true);
    }


    #[Route('/api/Person/{id}', name: 'deletePerson', methods: ['DELETE'])]
    public function deletePerson(Person $person, EntityManagerInterface $em): JsonResponse
    {
        if (!$em->contains($person)) {
            return new JsonResponse(['status' => false, 'message' => 'Person déjà supprimé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($person);
        $em->flush();

        return new JsonResponse(['status' => true, 'message' => 'Person supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }

    /// metre a jour Person

    #[Route('/api/Person/{id}', name:"updatePerson", methods:['PUT'])]
    public function updatePerson(Request $request, $id, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $person = $this->person->find($id);

        if (!$person) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'person non trouvé'
            ],
            Response::HTTP_NOT_FOUND
        );
    }

        $person->setFirstName($data['firstName'] ?? $person->getFirstName())
            ->setLastName($data['lastName'] ?? $person->getLastName())
            ->setTel($data['tel'] ?? $person->getTel());

    $em->persist($person);
    $em->flush();

    return new JsonResponse(
        [
            'status' => true,
            'message' => 'person mis à jour avec succès'
        ]
    );
}

     // liste des person
    #[Route('/api/getAllPersons', name: 'get_allPersons', methods:'GET')]
    public function GetAllPersons(Request $request,SerializerInterface $serializer): JsonResponse
    {

        $persons=$this->person->findAll();
        $jsonPersonsList = $serializer->serialize($persons, 'json', ['groups'=> "getPersons"]);
        return new JsonResponse($jsonPersonsList, Response::HTTP_OK, [], true);
    }
}
