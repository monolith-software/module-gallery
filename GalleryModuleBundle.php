<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery;

use Monolith\Bundle\CMSBundle\Module\ModuleBundle;
use Monolith\Module\Gallery\Entity\Gallery;

class GalleryModuleBundle extends ModuleBundle
{
    protected $adminMenuBeforeCode = '<i class="fa fa-object-ungroup"></i>';

    /**
     * Получить виджеты для рабочего стола.
     *
     * @return array
     */
    public function getDashboard(): array
    {
        $em     = $this->container->get('doctrine.orm.default_entity_manager');
        $r      = $this->container->get('router');

        $data = [
            'title' => 'Фотогалерея',
            'items' => [],
        ];

        foreach ($em->getRepository(Gallery::class)->findAll() as $gallery) {
            $data['items']['edit_slider_'.$gallery->getId()] = [
                'title' => 'Редактировать галерею: <b>'.$gallery->getTitle().'</b>',
                'descr' => '',
                'url'   => $r->generate('monolith_module.gallery.admin_gallery', ['id' => $gallery->getId()]),
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getRequiredParams(): array
    {
        return [
            'gallery_id',
        ];
    }
}
