<?php

namespace App\Controller;

use App\Entity\SubComment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubCommentController extends AbstractController
{
    /**
     * @Route("/sub_comment/create", name="app_sub_comment_create")
     */
    public function create(EntityManagerInterface $em, Request $request, CommentRepository $commentRepository, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $subCommentSentence = $request->request->get('subComment');
            $commentId = $request->request->getInt('commentId');

            $subComment = new SubComment;
            $subComment
                ->setSubComment($subCommentSentence)
                ->setComment($commentRepository->find($commentId))
                ->setAuthor($this->getUser());
            $errors = $validator->validate($subComment);
            if (count($errors) === 0) {
                $em->persist($subComment);
                $em->flush();
                return $this->redirectToRoute('app_question_show', [
                    'id' => $subComment->getComment()->getAnswer()->getQuestion()->getId()
                ]);
            }

            //Display error messages
            foreach ($errors as $error) {
                $this->addFlash('yellow', $error->getMessage());
                return $this->redirectToRoute('app_home');
            }
        }
        return $this->redirectToRoute('app_home');
    }
}
