<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Form\Type;

use Monolith\Module\Gallery\Entity\Gallery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('order_albums_by', ChoiceType::class, [
                'choices' => [
                    'По дате создания' => 0,
                    'По заданной позиции (по возрастанию)' => 1,
                    'По заданной позиции (по убыванию)' => 2,
                    'По дате последнего обновления' => 3, // @todo
                ],
                'choice_translation_domain' => false,
            ])
            ->add('media_collection')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'monolith_module_gallery';
    }
}
