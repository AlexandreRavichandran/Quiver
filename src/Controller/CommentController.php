<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\AnswerRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CommentController extends AbstractController
{
    private $em;
    private $commentRepository;

    public function __construct(EntityManagerInterface $em, CommentRepository $commentRepository)
    {
        $this->em = $em;
        $this->commentRepository = $commentRepository;
    }

    /*****************  API REQUEST METHODS *****************/

    /**
     * 
     * @Route("/api/comments/create", name="api_comment_create",methods="POST")
     * 
     * @param Request $request Request sent to this method
     * @param AnswerRepository $answerRepository Repository of the Answer entity
     * @param ValidatorInterface $validator Validator to check if entity is correctly filled
     * 
     * @return JsonResponse
     */
    public function create(AnswerRepository $answerRepository, Request $request, ValidatorInterface $validator): JsonResponse
    {
        // Check if request method is correct
        if (!$request->isMethod('POST')) {
            
            $responseCode = 405;

            //Prepare datas for success alert message
            $label = 'errorMessage';
            $messageText = 'Une erreur s\'est produite';
            $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData = [
                'message' => $message
            ];

            return new JsonResponse($jsonData, $responseCode);
        }

        $datas = json_decode($request->getContent());
        $csrfToken = $datas->csrf;

        //Check csrf
        if (!$this->isCsrfTokenValid('create_comment', $csrfToken)) {
            $label = 'errorMessage';
            $responseCode = 403;
            $messageText = 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.';
            $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            $jsonData = [
                'message' => $message
            ];
            return new JsonResponse($jsonData, $responseCode);
        }

        $comment = new Comment;
        $comment
            ->setAnswer($answerRepository->find($datas->answerId))
            ->setComment($datas->comment)
            ->setCreatedAt(new DateTimeImmutable())
            ->setAuthor($this->getUser());

        $errors = $validator->validate($comment);

        //Check if there is any error on creating comment
        if (0 !== count($errors)) {
            $label = 'errorMessage';
            $responseCode = 400;
            foreach ($errors as $key => $error) {
                $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
            }

            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }

        $this->em->persist($comment);
        $this->em->flush();
        
        //Prepare datas for success alert message
        $responseCode = 201;
        $label = 'successMessage';
        $messageText = 'Votre commentaire a été postée avec succès.';
        $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
        $jsonData = [
            'comment' => $comment->getComment(),
            'user' => $this->getUser()->getPseudonym(),
            'date' => $comment->getCreatedAt()->format('d/m/Y')
        ];

        $jsonData['message'] = $message;

        return new JsonResponse($jsonData, $responseCode);
    }

    /**
     * Generate more comments from a date
     * 
     * @Route("/api/answer/comments/{id}/{date}",name="api_comment_generate",methods="GET",requirements={"id"="\d+","date"="\d{4}-\d{2}-\d{2}"})
     * 
     * @param integer $id id of the answer
     * @param string|null $date Date of the last showed comment. date format must be YYYY-MM-DD
     * 
     * @return JsonResponse
     */
    public function generate(int $id, string $date = null):JsonResponse
    {
        if (null === $date) {
            $date = new DateTimeImmutable();
        } else {
            $date = new DateTimeImmutable($date);
        }
        $comments = $this->commentRepository->findCommentByAnswer($id, $date, 2);

        $jsonData = [
            'content' => $this->renderView('partials/_comments_subcomments.html.twig', ['comments' => $comments])
        ];

        return new JsonResponse($jsonData);
    }
}

