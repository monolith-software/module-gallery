<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Controller;

use Monolith\Bundle\CMSBundle\Annotation\NodePropertiesForm;
use Monolith\Bundle\CMSBundle\Entity\Node;
use Monolith\Module\Gallery\Entity\Album;
use Monolith\Module\Gallery\Entity\Gallery;
use Monolith\Module\Gallery\Entity\Photo;
use Smart\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    /**
     * @param Node $node
     * @param int  $gallery_id
     *
     * @return Response
     *
     * @NodePropertiesForm("NodePropertiesFormType")
     */
    public function indexAction(Node $node, $gallery_id): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        switch ($em->find(Gallery::class, $gallery_id)->getOrderAlbumsBy()) {
            case 1:
                $albumsOrderBy = ['position' => 'ASC'];
                break;
            case 2:
                $albumsOrderBy = ['position' => 'DESC'];
                break;
            default:
                $albumsOrderBy = ['id' => 'DESC'];
        }

        $albums = $em->getRepository(Album::class)->findBy(['is_enabled' => true, 'gallery' => $gallery_id], $albumsOrderBy);

        $node->addFrontControl('manage_gallery')
            ->setTitle('Управление фотогалереей')
            ->setUri($this->generateUrl('monolith_module.gallery.admin_gallery', ['id' => $gallery_id]));

        return $this->render('@GalleryModule/index.html.twig', [
            'albums'  => $albums,
        ]);
    }

    /**
     * @param int $id
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function albumAction(Node $node, $id): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository(Album::class)->find($id);

        if (empty($album) or $node->getParam('gallery_id') != $album->getGallery()->getId() or $album->isDisabled()) {
            throw $this->createNotFoundException();
        }

        $node->addFrontControl('manage_album')
            ->setTitle('Редактировать фотографии')
            ->setUri($this->generateUrl('monolith_module.gallery.admin_album', [
                'id' => $album->getId(),
                'gallery_id' => $node->getParam('gallery_id'),
            ]));

        $this->get('cms.breadcrumbs')->add((string) $album->getId(), $album->getTitle());

        $photos = $em->getRepository(Photo::class)->findBy(['album' => $album], ['id' => 'DESC']);

        return $this->render('@GalleryModule/photos.html.twig', [
            'photos'  => $photos,
        ]);
    }

    /**
     * @param Node $node
     * @param int  $count
     */
    public function latestWidgetAction(Node $node, int $count = 5): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $photos = $em->getRepository(Photo::class)->findBy([], ['id' => 'DESC'], $count);

        $node->addFrontControl('manage_album')
            ->setTitle('Редактировать фотографии')
            ->setUri($this->generateUrl('monolith_module.gallery.admin'));

        return $this->render('@GalleryModule/latest_widget.html.twig', [
            'photos'  => $photos,
        ]);
    }
}
