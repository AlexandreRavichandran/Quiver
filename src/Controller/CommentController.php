<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\UserRepository;
use App\Repository\AnswerRepository;
use App\Repository\CommentRepository;
use DateTimeImmutable;
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


    /*****************  API REQUEST METHODS *****************/

    /**
     * @Route("/comments/create", name="api_comment_create",methods="POST")
     * @return JsonResponse
     */
    public function create(AnswerRepository $answerRepository, EntityManagerInterface $em, Request $request, UserRepository $user, ValidatorInterface $validator): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $datas = json_decode($request->getContent());
            $csrfToken = $datas->csrf;
            if (!$this->isCsrfTokenValid('create_comment', $csrfToken)) {
                $label = 'errorMessage';
                $responseCode = 403;
                $messageText = 'le serveur a détecté une attaque CSRF et l\'operation a été abandonnée.';
                $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
            } else {
                $comment = new Comment;
                $comment
                    ->setAnswer($answerRepository->find($datas->answerId))
                    ->setComment($datas->comment)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setAuthor($this->getUser());

                //Check if there is any error on creating answer
                $errors = $validator->validate($comment);

                if (count($errors) === 0) {
                    $em->persist($comment);
                    $em->flush();
                    $responseCode = 201;

                    //Prepare datas for success alert message
                    $label = 'successMessage';
                    $messageText = 'Votre commentaire a été postée avec succès.';
                    $message = $this->renderView('partials/_alert_message.html.twig', ['message' => $messageText, 'label' => $label]);
                    $jsonData = [
                        'comment' => $comment->getComment(),
                        'user' => $this->getUser()->getPseudonym(),
                        'date' => $comment->getCreatedAt()->format('d/m/Y')
                    ];
                } else {
                    $label = 'errorMessage';
                    $responseCode = 400;
                    foreach ($errors as $key => $error) {
                        $message[$key] = $this->renderView('partials/_alert_message.html.twig', ['message' => $error->getMessage(), 'label' => $label]);
                    }
                }
            }

            $jsonData['message'] = $message;

            return new JsonResponse($jsonData, $responseCode);
        }
    }

    /**
     * 
     * @Route("/answer/comments/{id}/{date}",name="api_comment_generate",methods="GET",requirements={"id"="\d+","date"="\d{4}-\d{2}-\d{2}"})
     */
    public function generate(int $id, string $date = null, CommentRepository $commentRepository)
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        } else {
            $date = new DateTimeImmutable($date);
        }
        $comments = $commentRepository->findCommentByAnswer($id, $date, 2);
        $jsonData = [
            'content' => $this->renderView('partials/_comments_subcomments.html.twig', ['comments' => $comments])
        ];

        return new JsonResponse($jsonData);
    }
}
