<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ResponseQ;
use App\Entity\User;
use App\Entity\Question;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="api_")
 */


class ResponseController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    /**
     * @Route("/response", methods={"GET"}, name="response")
     */
    public function getResponse(EntityManagerInterface $e){
        $response = $e->getRepository(ResponseQ::class)->findAll();
        return $this->json($response, Response::HTTP_OK);


    }
    /**
     * @Route("/response/{id}", methods={"GET"}, name="response_id")
     */
    public function getResponseById(EntityManagerInterface $e, $id){
        $response = $e->getRepository(ResponseQ::class)->find($id);
        return $this->json($response, Response::HTTP_OK);
    }
    /**
     * @Route("/response", methods={"POST"}, name="response_add")
     */
    public function addResponse(EntityManagerInterface $e, Request $request){
        $data=json_decode($request->getContent(), true);
        dump($data);
        $response = new ResponseQ();
        $user = $this->security->getUser();

        $response->setContent($data['content']);
        $response->setUserId($user);
        $response->setDate(new \DateTimeImmutable());
        $question=$e->getRepository(\App\Entity\Question::class)->find($data['question_id']);
        $response->setQuestionId($question);
        $e->persist($response);
        $e->flush();
        return $this->json($data, Response::HTTP_CREATED);
    }
    /**
     * @Route("/response/{id}", methods={"PUT"}, name="response_update")
     */
    public function update(EntityManagerInterface $e, Request $request, $id){
        $data=$request->request->all();
        $response = $e->getRepository(ResponseQ::class)->find($id);
        $response->setContent($data['content']);
        $response->setQuestionId($data['question_id']);
        $e->persist($response);
        $e->flush();
        return $this->json($data, Response::HTTP_OK);
    }
    /**
     * @Route("/response/{id}", methods={"DELETE"}, name="response_delete")
     */
    public function deleteResponse(EntityManagerInterface $e, $id){
        $response = $e->getRepository(ResponseQ::class)->find($id);
        $e->remove($response);
        $e->flush();
        return $this->json(null, Response::HTTP_OK);
}
/**
 * @Route("/responseq/{id}", methods={"GET"}, name="response_question_id")
 */
public function getResponseByQuestionId(EntityManagerInterface $e, $id){
    $response = $e->getRepository(ResponseQ::class)->findBy(['question_id'=>$id]);
    return $this->json($response, Response::HTTP_OK);}

}