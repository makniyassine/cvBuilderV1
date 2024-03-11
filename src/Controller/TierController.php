<?php

namespace App\Controller;

use App\Entity\Tier;
use App\Repository\TierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TierController extends AbstractController
{
    private $manager;
    private $Tier;
    public function __construct(EntityManagerInterface $manager , TierRepository $tier)
    {
        $this->manager=$manager;

        $this->Tier=$tier;

    }
    
    
    

    // creation d'un utilisateur
    #[Route('/TierCreate', name: 'createTier', methods:'POST')]
    public function TierCreate(Request $request): JsonResponse
    {

        $data=json_decode($request->getContent(),true);
        $email=$data['email'];
        $raisonSociale=$data["raisonSociale"];
        $siteWeb=$data["siteWeb"] ;
        $createdAt=new \DateTimeImmutable();
        $updatedAt=new \DateTimeImmutable();
        //verifier si l'email existe deja

        $email_exist=$this->Tier->findOneByEmail($email);
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
            $tier=new Tier();
            $tier->setEmail($email)
                    ->setRaisonSociale($raisonSociale)
                    ->setSiteWeb($siteWeb)
                    ->setCreatedAt($createdAt)
                    ->setUpdatedAt($updatedAt);

            $this->manager->persist( $tier);
            $this->manager->flush();

            return new JsonResponse
            (

                [
                    'status'=>true,
                    'message'=>'tier cree avec success'
                ]
            );
        }
    }

    #[Route('/api/Tiers/{firstName}', name: 'getTier', methods: ['GET'])]
    public function getDetailTier(Tier $tier, SerializerInterface $serializer): JsonResponse
    {
        $jsonTiers = $serializer->serialize($tier, 'json', ['groups'=> "getTiers"]);
        return new JsonResponse($jsonTiers, Response::HTTP_OK, [], true);
    }


    #[Route('/api/Ties/{id}', name: 'deleteTier', methods: ['DELETE'])]
    public function deleteTier(Tier $tier, EntityManagerInterface $em): JsonResponse
    {
        if (!$em->contains($tier)) {
            return new JsonResponse(['status' => false, 'message' => 'Tier déjà supprimé'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($tier);
        $em->flush();

        return new JsonResponse(['status' => true, 'message' => 'Tier supprimé avec succès'], Response::HTTP_NO_CONTENT);
    }


    /// metre a jour user

    #[Route('/api/Tiers/{id}', name:"updateTiers", methods:['PUT'])]
    public function updateTier(Request $request, $id, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tier = $this->Tier->find($id);

        if (!$tier) {
            return new JsonResponse(
                [
                    'status' => false,
                    'message' => 'tier non trouvé'
            ],
            Response::HTTP_NOT_FOUND
        );
    }

        $tier->setRaisonSociale($data['raisonSociale'] ?? $tier->getRaisonSociale())
            ->setSiteWeb($data['siteWeb'] ?? $tier->getSiteWeb());

    $em->persist($tier);
    $em->flush();

    return new JsonResponse(
        [
            'status' => true,
            'message' => 'Tier mis à jour avec succès'
        ]
    );
}

     // liste des utilisateur
    #[Route('/api/getAllTiers', name: 'get_allTiers', methods:'GET')]
    public function GetAllUser(Request $request,SerializerInterface $serializer): JsonResponse
    {

        $tiers=$this->Tier->findAll();
        $jsonUsersList = $serializer->serialize($tiers, 'json', ['groups'=> "getTiers"]);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }
}

