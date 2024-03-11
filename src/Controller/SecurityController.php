<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\Json;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login' , methods:["POST"])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $user = $userRepository->findOneByEmail($email);
    
        if (!$user) {
            return new JsonResponse('Email not found', Response::HTTP_BAD_REQUEST);
        }
    
        if (!$user->isEmailConfirmed() || !$user->isEnabled()) {
            return new JsonResponse('Email not confirmed or account not enabled', Response::HTTP_BAD_REQUEST);
        }
    
        if (!$userPasswordHasher->hashPassword($user, $password)) {
            return new JsonResponse('Invalid password', Response::HTTP_BAD_REQUEST);
        }
    
        // Handle login logic, e.g., setting session variables or redirecting
    
        return new JsonResponse('Login successful', Response::HTTP_OK);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
