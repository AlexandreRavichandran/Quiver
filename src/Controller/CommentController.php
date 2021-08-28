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
    /**
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/comments/create", name="app_comment_create")
     */
    public function create(AnswerRepository $answerRepository, EntityManagerInterface $em, Request $request, UserRepository $user, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $commentSentence = $request->request->get('comment');
            $answerId = $request->request->getInt('answerId');

            $comment = new Comment;
            $comment
                ->setAnswer($answerRepository->find($answerId))
                ->setComment($commentSentence)
                ->setAuthor($this->getUser());
            $errors = $validator->validate($comment);
            if (count($errors) === 0) {
                $em->persist($comment);
                $em->flush();
                return $this->redirectToRoute('app_question_show', [
                    'id' => $comment->getAnswer()->getQuestion()->getId()
                ]);
            }

            //Check if the referer route is valid
            try {
                $redirectRoute =  $this->generateUrl($request->request->get('referer'));
                $redirectRoute = $request->request->get('referer');
            } catch (RouteNotFoundException $e) {
                $redirectRoute = 'app_home';
            }

            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('yellow', $error->getMessage());
            }
        }
        return $this->redirectToRoute('app_home');
    }

    /**
     * Undocumented function
     * @Route("/answer/comments/{id}/{date}",name="app_comments_generate")
     */
    public function getMoreComments(int $id, string $date = null, CommentRepository $commentRepository)
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        }
        $comments = $commentRepository->findCommentByAnswer($id, $date, 2);

        $jsonData = [
            'content' => $this->renderView('partials/_comments_subcomments.html.twig', ['comments' => $comments])
        ];
        return new JsonResponse($jsonData);
    }
}
