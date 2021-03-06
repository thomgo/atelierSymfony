<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Subject;
use App\Entity\Answer;
use App\Entity\Comment;
use App\Form\SubjectType;
use App\Form\AnswerType;
use App\Form\CommentType;
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
        // On récupère le repo (le manger/model) de l'entité Subject, ce repo contient déjà des requêtes simples en BDD
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        // Sur le repo on appelle la méthode findAll qui renvoie toutes les entités (ici Subject)
        $subjects = $subjectRepository->findAll();
        // On retourne une vue sous forme de réponse et on lui passe une variables subjects à laquelle on associe $subjects
        return $this->render('forum/index.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * @Route("/user/subjects", name="user_subjects")
     */
    public function user_subjects(): Response
    {
        // On retourne une vue sous forme de réponse et on lui passe une variables subjects à laquelle on associe $subjects
        return $this->render('forum/user_subjects.html.twig');
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
    // Méthode pour afficher un sujet. Elle attend un paramètre id car la route attend un paramètre
    // On précise dans la route et la méthode que ce paramètre est un integer
    public function subject(int $id, Request $request): Response
    {
        $subjectRepository = $this->getDoctrine()->getRepository(Subject::class);
        // On fait appelle à la méthode find du repo qui recherche une entité par sa clef primaire
        $subject = $subjectRepository->findOneSubject($id);

        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
          // Associe l'utilisateur connecté  à la réponse
          $answer->setUser($this->getUser());
          // Associe la réponse au sujet
          $answer->setSubject($subject);
          $answer->setAnswerDate(new \DateTime());
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($answer);
          $entityManager->flush();
        }
        // On passe l'entité récupérée à la vue
        return $this->render('forum/subject.html.twig', [
            'subject' => $subject,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/subject/new", name="new_subject")
     */
    // Pour fonctionner notre méthode attend des instance de Request et ValidatorInterface
    public function newSubject(Request $request, ValidatorInterface $validator): Response
    {
        // Par défaut erreurs à null, cela permet de déclarer la variable et la passer à la vue quoiqu'il arrive
        $errors = null;
        // On crée un nouveau Subject vide et un formulaire sur la base de cette entité
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        // On traite les données soumises lors de la requêtes dans l'objet form
        $form->handleRequest($request);
        // Si on a soumis un formulaire et que tout est OK
        if ($form->isSubmitted() && $form->isValid()) {
          // On associe le sujet à l'utilisateur connecté
          $subject->setUser($this->getUser());
          // On vérifie la présence d'erreur dans l'objet sur la base des règles définies par le  validateur dans l'entité
          $errors = $validator->validate($subject);
          // Si le tableau ne contient pas d'erreurs
          if(count($errors) === 0) {
            // On enregistre le nouveau sujet
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subject);
            // Attention les requêtes ne sont exécutées que lors du flush donc à ne pas oublier
            $entityManager->flush();
            // On crée des message de succès en session appelés flash messages
            $this->addFlash('success','Votre question a bien été enregistrée');
            $this->addFlash('success', "N'hésitez pas à visiter le forum");
            // On redirige vers la route de nom subjects (cad l'accueil)
            return $this->redirectToRoute('subjects');
          }
        }
        // Attention dans la vue on ne passe pas l'objet form mais une vue html affichable
        return $this->render('forum/new_subject.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/comment/{id}", name="comment")
     */
    public function comment(int $id, Request $request): Response
    {
        $answerRepository = $this->getDoctrine()->getRepository(Answer::class);
        $answer = $answerRepository->find($id);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
          $comment->setAnswer($answer);
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($comment);
          $entityManager->flush();
          return $this->redirectToRoute("subject", ["id" => $answer->getSubject()->getId()]);
        }
        return $this->render('forum/comment.html.twig', [
            'answer' => $answer,
            'form' => $form->createView()
        ]);
    }
}
