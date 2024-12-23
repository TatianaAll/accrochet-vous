<?php

namespace App\Form;

use App\Entity\Admin;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Status;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('image',FileType::class, ['mapped'=>false,
                'required' => false,])
//            ->add('tags', EntityType::class, [
//                'class' => Tag::class,
//                'choice_label' => 'id',
//                'multiple' => true,
//            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
            ])

            ->add('enregistrer', SubmitType::class)
        ;
        //for manage the status I need distinction between admin and users
        if ($options['is_admin']) {
            $builder->add('status', ChoiceType::class, [
                'choices' => [
                    'Publié' => 'published',
                    'A modérer' => 'to moderate',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'is_admin' => false,
        ]);
    }
}
