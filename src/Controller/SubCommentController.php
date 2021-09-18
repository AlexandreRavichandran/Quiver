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
use Symfony\Component\HttpFoundation\JsonResponse;

class SubCommentController extends AbstractController
{

    /*****************  API REQUEST METHODS *****************/

    /**
     * @Route("/subComments/create", name="api_subcomment_create",methods="POST")
     */
    public function create(EntityManagerInterface $em, Request $request, CommentRepository $commentRepository, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $datas = json_decode($request->getContent());

            $subComment = new SubComment;
            $subComment
                ->setSubComment($datas->subComment)
                ->setComment($commentRepository->find($datas->commentId))
                ->setAuthor($this->getUser());
            $errors = $validator->validate($subComment);
            if (count($errors) === 0) {
                $em->persist($subComment);
                $em->flush();
                $jsonData = [
                    'comment' => $subComment->getSubComment(),
                    'user' => $this->getUser()->getPseudonym(),
                    'date' => $subComment->getCreatedAt()->format('d/m/Y')
                ];
                return new JsonResponse($jsonData, 200);
            }

        }

    }
}
