<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Subject;
use App\Form\SubjectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("IS_AUTHENTICATED_FULLY")
*/
class ForumController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("/subjects", name="subjects")
     */
    public function index(): Response
    {
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        $subjects = $subjectRepository->findAll();

        return $this->render('forum/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/rules", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('forum/rules.html.twig', [
            'controller_name' => 'ForumController',
        ]);
    }

    /**
     * @Route("/subject/{id}", name="subject", requirements={"id"="\d+"})
     */
    public function subject(int $id): Response
    {
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        $subject = $subjectRepository->find($id);

        return $this->render('forum/subject.html.twig', [
            'subject' => $subject,
        ]);
    }

    /**
     * @Route("/subject/new", name="new_subject")
     */
    public function newSubject(Request $request, ValidatorInterface $validator): Response
    {
        $errors = null;
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        // On traite les données de la requêtes dans l'objet form
        $form->handleRequest($request);
        // Si on a soumis un formulaire et tout est OK
        if ($form->isSubmitted() && $form->isValid()) {

          $errors = $validator->validate($subject);
          if(count($errors) === 0) {
            // On enregistre le nouveau sujet
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subject);
            $entityManager->flush();
            $this->addFlash('success','Votre question a bien été enregistrée');
            $this->addFlash('success', "N'hésitez pas à visiter le forum");
            return $this->redirectToRoute('subjects');
          }
        }

        return $this->render('forum/new_subject.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }
}
