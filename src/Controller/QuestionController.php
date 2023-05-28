<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Security;



/**
 * @Route("/api", name="api_")
 */

class QuestionController extends AbstractController
{
    private $security;

public function __construct(Security $security)
{
    $this->security = $security;
}
    /**
 * @Route("/questions", methods={"POST"})
 */
   
public function addQuestion(Request $request, EntityManagerInterface $entityManager,ValidatorInterface $validator)
{
    $question = new Question();

    $user = $this->security->getUser();
   

   $data = $request->request->all();
  

    $question->setTitre($data['titre']);
    $question->setDescription($data['description']);
    
    $question->setDate(new \DateTimeImmutable());
        /** @var UploadedFile $file */
     $file=$request->files->get('img');
    $fileName=md5(uniqid()).'.'.$file->guessExtension();
    $baseurl=$request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
    $question->setImg($baseurl.'/image/'.$fileName);
    $question->setUserId($user);
    $errors = $validator->validate($question);
    
    if (count($errors) > 0) {
        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);}else{
    $file->move($this->getParameter('upload_directory'),$fileName);

    $entityManager->persist($question);
    $entityManager->flush();}

    return new JsonResponse($question, Response::HTTP_CREATED);
}

   /**
     * @Route("/questions", methods={"GET"}, name="questions_list")
     */
    public function getQuestions(EntityManagerInterface $entityManager): JsonResponse
    
    {
       
        $questions = $entityManager->getRepository(Question::class)->findAll();

        return $this->json($questions, 200);
        
    }
    /**
     * @Route("/questions/{id}", methods={"GET"}, name="questions_show")
     */
    public function getQuestion(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $question = $entityManager->getRepository(Question::class)->find($id);
        return $this->json($question, 200);
    }
    /**
     * @Route("/questions/{id}", methods={"PUT"}, name="questions_update")
     */ 
    public function updateQuestion(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $question = $entityManager->getRepository(Question::class)->find($id);

 
        #return titre from request
       $data = $request->request->all();
    
        $question->setTitre($data['titre']);
        $question->setDescription($data['description']);
        $question->setDate(new \DateTime($data['date']));
        #remove old image
        $oldImage=$question->getImg();
        $oldImage=basename($oldImage);
        $fs = new Filesystem();
        $fs->remove($this->getParameter('upload_directory').'/'.$oldImage);

            /** @var UploadedFile $file */
         $file=$request->files->get('img');
        $fileName=md5(uniqid()).'.'.$file->guessExtension();
        $baseurl=$request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
        $question->setImg($baseurl.'/image/'.$fileName);
        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        $question->setUserId($user);
        $errors = $validator->validate($question);
        if (count($errors) > 0) {
            return $this->json(['errors' => count($errors)], Response::HTTP_BAD_REQUEST);}
        else{
        $fileName=md5(uniqid()).'.'.$file->guessExtension();
        $file->move($this->getParameter('upload_directory'),$fileName);
        $entityManager->persist($question);
        $entityManager->flush();}
    
        return new Response('Question added', Response::HTTP_CREATED);
    }


     /**
     * @Route("/user", methods={"GET"}, name="questions_delete")
     */     
    public function getuserdatials(EntityManagerInterface $entityManager)
     {
         $user=$this->security->getUser();
         return $this->json($user, 200);
     }

    

  /**
 * @Route("/search", methods={"POST"}, name="search")
 */
public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $titre = $data['titre'];

    $repository = $entityManager->getRepository(Question::class);
    $queryBuilder = $repository->createQueryBuilder('q');

    $query = $queryBuilder
        ->where($queryBuilder->expr()->like('q.titre', ':titre'))
        ->setParameter('titre', '%' . $titre . '%')
        ->getQuery();

    $questions = $query->getResult();

    return $this->json($questions, 200);
}
    /**
     * @Route("/question/{id}", methods={"DELETE"}, name="questions")
     */
    public function delete(EntityManagerInterface $e,int $id)
    {
            
        $question = $e->getRepository(Question::class)->find($id);
        
        if (!$question) {
            return $this->json(['message' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }
        if(!$question->getImg()){
        $oldImage = $question->getImg();
        $oldImage = basename($oldImage);
        $fs = new Filesystem();
        $fs->remove($this->getParameter('upload_directory').'/'.$oldImage);}
    
        $e->remove($question);
        $e->flush();
    
        return $this->json(['message' => 'Question deleted'], Response::HTTP_NO_CONTENT);
    }
 /**
     * @Route("/questionsuser", methods={"GET"}, name="questionsuser")
     */
    public function getQuestionsbyuser(EntityManagerInterface $entityManager): JsonResponse
    
    {
     $user=$this->security->getUser();
      $questions = $entityManager->getRepository(Question::class)->findBy(['user_id'=>$user]);

      return $this->json($questions, 200);
      
    }

}
