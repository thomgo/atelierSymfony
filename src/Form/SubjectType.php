<?php

namespace App\Form;

use App\Entity\Subject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Classe formulaire pour l'entité Subject, ce formulaire a vocation à hydrater un objet Subject
class SubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      // Cette méthode crée notre formualire, c'est ici qu'on ajoute nos différents champs
      // De nous ne sommes pas obligés de spécifier le type car les champs correspondent à une entité
      // Par contre pour submit cela est nécessaire
        $builder
            ->add('title')
            ->add('content')
            ->add('save', SubmitType::class, [
              'label' => 'Posez votre question',
              'attr' => [
                "class" => "btn btn-danger"
              ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          // ici on associé le formulaire à la classe Subject
            'data_class' => Subject::class,
        ]);
    }
}
