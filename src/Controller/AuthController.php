<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * @Route("/api", name="api_")
 */

class AuthController extends AbstractController
{
    private $passwordEncoder;
    private $security;
    public function __construct(UserPasswordHasherInterface $passwordEncoder,Security $security)
    { 
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        SerializerInterface $serializerInterface,
        EntityManagerInterface $entityManagerInterface
    ): Response {
       $data=json_decode($request->getContent(), true);
       
        $user=$entityManagerInterface->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if($user){
            return $this->json($message="email already exist", Response::HTTP_BAD_REQUEST);
        }
        $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));

        $entityManagerInterface->persist($user);
        $entityManagerInterface->flush();

        return $this->json($message="your acoount has create with sucess", 200);
    }

    #[Route('api/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): Response
    {
        return $this->json(['message' => 'Logged out'], 200);
    }
     /**
     * @Route("/changedpassword", methods={"POST"}, name="changedpassword")
     
     */
    public function changedpassword(EntityManagerInterface $e, Request $request){
        $data=json_decode($request->getContent(), true);
        $user = $this->security->getUser();
        if($this->passwordEncoder->isPasswordValid($user, $data['oldpassword'])){
            $user->setPassword($this->passwordEncoder->hashPassword($user, $data['newpassword']));
            $e->persist($user);
            $e->flush();
            return $this->json($message="password changed succesfully", Response::HTTP_CREATED);
        }
        else{
            return $this->json($message="oldpassword is incorrect", Response::HTTP_BAD_REQUEST);
        }
     
    }
   

 
}
