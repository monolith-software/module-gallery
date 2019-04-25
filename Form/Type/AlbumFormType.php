<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Form\Type;

use Monolith\Module\Gallery\Entity\Album;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlbumFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('is_enabled', null, ['required' => false])
            ->add('title')
            ->add('position')
            ->add('description')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'monolith_module_gallery_album';
    }
}
