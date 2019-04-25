<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Form\Type;

use Monolith\Bundle\CMSBundle\Module\AbstractNodePropertiesFormType;
use Monolith\Module\Gallery\Entity\Gallery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class NodePropertiesFormType extends AbstractNodePropertiesFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $galleries = [];
        foreach ($this->em->getRepository(Gallery::class)->findAll() as $gallery) {
            $galleries[(string) $gallery] = $gallery->getId();
        }

        $builder
            ->add('gallery_id', ChoiceType::class, [
                'choices' => $galleries,
                'choice_translation_domain' => false,
                'label' => 'Gallery',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'monolith_module_gallery_node_properties';
    }
}
