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
     * @Route("/comments/create", name="app_comment_create")
     */
    public function create(AnswerRepository $answerRepository, EntityManagerInterface $em, Request $request, UserRepository $user, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $datas = json_decode($request->getContent());
            $comment = new Comment;
            $comment
                ->setAnswer($answerRepository->find($datas->answerId))
                ->setComment($datas->comment)
                ->setCreatedAt(new DateTimeImmutable())
                ->setAuthor($this->getUser());
            $errors = $validator->validate($comment);
            if (count($errors) === 0) {
                $em->persist($comment);
                $em->flush();
                $jsonData = [
                    'comment' => $comment->getComment(),
                    'user' => $this->getUser()->getPseudonym(),
                    'date' => $comment->getCreatedAt()->format('d/m/Y')
                ];
                return new JsonResponse($jsonData, 200);
            }

            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('yellow', $error->getMessage());
                return $this->redirectToRoute('app_home');
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
