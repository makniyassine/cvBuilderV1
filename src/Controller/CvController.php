<?php

namespace App\Controller;

use App\Entity\Cv;
use App\Repository\CvRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CvController extends AbstractController
{
    private $manager;
    private $Cv;
    public function __construct(EntityManagerInterface $manager , CvRepository $cv, )
    {
        $this->manager=$manager;

        $this->Cv=$cv;

    }
    
    
    

    // creation d'un utilisateur
    #[Route('/CreateCv', name: 'creatCv', methods:'POST')]
    public function createCv(Request $request): JsonResponse
    {

        $data=json_decode($request->getContent(),true);
        $name=$data["name"] ;
        $createdAt=new \DateTimeImmutable();
        $updatedAt=new \DateTimeImmutable();
        //verifier si l'email existe deja

        $name_exist=$this->Cv->findOneByname($name);
        if($name_exist)
        {
            return new JsonResponse
            (

                [
                    'status'=>false,
                    'message'=>'Cet Cv exist deja'
                ]
            );
        }
        else
        {
            $cv=new Cv();
            $cv->setName($name)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($updatedAt);

            $this->manager->persist( $cv);
            $this->manager->flush();

            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'cv cree avec success'
                ]
            );
        }
    }

    #[Route('/api/Cv/{name}', name: 'getCv', methods: ['GET'])]
    public function getDetailCv(Cv $cv, SerializerInterface $serializer): JsonResponse
    {
        $jsonCv = $serializer->serialize($cv, 'json', ['groups'=> "getCvs"]);
        return new JsonResponse($jsonCv, Response::HTTP_OK, [], true);
    }


    #[Route('/api/Cv/{id}', name: 'deleteCv', methods: ['DELETE'])]
    public function deleteCv(Cv $cv, EntityManagerInterface $em): JsonResponse
    {
        if (!$em->contains($cv)) {
            return new JsonResponse(['status' => false, 'message' => 'cv déjà supprimé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($cv);
        $em->flush();

        return new JsonResponse(['status' => true, 'message' => 'cv supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }

    /// metre a jour Person

    #[Route('/api/Cv/{id}', name:"updateCV", methods:['PUT'])]
    public function updateCV(Request $request, $id, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $cv = $this->Cv->find($id);

        if (!$cv) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'Cv non trouvé'
            ],
            Response::HTTP_NOT_FOUND
        );
    }

        $cv->setName($data['name'] ?? $cv->getName());
            

    $em->persist($cv);
    $em->flush();

    return new JsonResponse(
        [
            'status' => true,
            'message' => 'Cv mis à jour avec succès'
        ]
    );
}

     // liste des person
    #[Route('/api/getAllCvs', name: 'get_allCvs', methods:'GET')]
    public function GetAllPersons(Request $request,SerializerInterface $serializer): JsonResponse
    {

        $cvs=$this->Cv->findAll();
        $jsonCvsList = $serializer->serialize($cvs, 'json', ['groups'=> "getCvs"]);
        return new JsonResponse($jsonCvsList, Response::HTTP_OK, [], true);
    }
}