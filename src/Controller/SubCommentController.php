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
                $responseCode = 201;

                //Prepare datas for success alert message
                $message = 'Votre commentaire a été postée avec succès.';
                $label = 'successMessage';
                $jsonData = [
                    'comment' => $subComment->getSubComment(),
                    'user' => $this->getUser()->getPseudonym(),
                    'date' => $subComment->getCreatedAt()->format('d/m/Y')
                ];
            } else {
                $responsecode = 401;
                //Prepare datas for failure alert message
                $message = "Une erreur est survenu lors de la création de votre réponse. Veuillez essayer ulterieurement.";
                $label = 'errorMessage';
            }
            $jsonData[] = [
                'message' => $this->renderView('partials/_alert_message.html.twig', ['message' => $message, 'label' => $label])
            ];

            return new JsonResponse($jsonData, $responseCode);
        }
    }
}
