<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Controller;

use Smart\CoreBundle\Controller\Controller;
use Monolith\Module\Gallery\Entity\Album;
use Monolith\Module\Gallery\Entity\Gallery;
use Monolith\Module\Gallery\Entity\Photo;
use Monolith\Module\Gallery\Form\Type\AlbumFormType;
use Monolith\Module\Gallery\Form\Type\GalleryFormType;
use Monolith\Module\Gallery\Form\Type\PhotoFormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminGalleryController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request): Response
    {
        $gallery = new Gallery();
        $gallery->setUser($this->getUser());

        $form = $this->createForm(GalleryFormType::class, $gallery);
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Gallery created successfully.');

                return $this->redirectToRoute('monolith_module.gallery.admin');
            }
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        return $this->render('@GalleryModule/Admin/index.html.twig', [
            'form'      => $form->createView(),
            'galleries' => $em->getRepository('GalleryModuleBundle:Gallery')->findAll(),
        ]);
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function galleryAction(Request $request, Gallery $gallery): Response
    {
        $album = new Album();
        $album
            ->setGallery($gallery)
            ->setUser($this->getUser())
        ;

        $form = $this->createForm(AlbumFormType::class, $album);
        $form
            ->remove('is_enabled')
            ->add('create album', SubmitType::class, ['attr' => ['class' => 'btn-success']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Album created successfully.');

                return $this->redirectToRoute('monolith_module.gallery.admin_gallery', ['id' => $gallery->getId()]);
            }
        }

        $folderPath = null;
        foreach ($this->get('cms.node')->findByModule('GalleryModuleBundle') as $node) {
            if ($node->getParam('gallery_id') === (int) $gallery->getId()) {
                $folderPath = $this->get('cms.folder')->getUri($node);

                break;
            }
        }

        $albumOrderBy = ['id' => 'DESC']; // Gallery::ORDER_BY_CREATED

        if ($gallery->getOrderAlbumsBy() == Gallery::ORDER_BY_POSITION_ASC) {
            $albumOrderBy = ['position' => 'ASC'];
        } elseif ($gallery->getOrderAlbumsBy() == Gallery::ORDER_BY_POSITION_DESC) {
            $albumOrderBy = ['position' => 'DESC'];
        } elseif ($gallery->getOrderAlbumsBy() == Gallery::ORDER_BY_LAST_PHOTO) {
            $albumOrderBy = ['updated_at' => 'DESC'];
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        return $this->render('@GalleryModule/Admin/gallery.html.twig', [
            'form'       => $form->createView(),
            'folderPath' => $folderPath,
            'albums'     => $em->getRepository(Album::class)->findBy(['gallery' => $gallery], $albumOrderBy),
            'gallery'    => $gallery,
        ]);
    }

    /**
     * @param Request $request
     * @param Gallery $gallery
     *
     * @return Response
     */
    public function galleryEditAction(Request $request, Gallery $gallery): Response
    {
        $form = $this->createForm(GalleryFormType::class, $gallery);
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
             ->add('cancel', SubmitType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('monolith_module.gallery.admin');
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Gallery updated successfully.');

                return $this->redirectToRoute('monolith_module.gallery.admin');
            }
        }

        return $this->render('@GalleryModule/Admin/gallery_edit.html.twig', [
            'form'      => $form->createView(),
            'gallery'   => $gallery,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param int $gallery_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @todo pagination
     */
    public function albumAction(Request $request, $id, $gallery_id): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $album = $em->find(Album::class, $id);

        if (empty($album) or $album->getGallery()->getId() != $gallery_id) {
            throw $this->createNotFoundException();
        }

        $photo = new Photo();
        $photo
            ->setUser($this->getUser())
            ->setAlbum($album)
        ;

        $form = $this->createForm(PhotoFormType::class, $photo);
        $form->add('upload', SubmitType::class, ['attr' => ['class' => 'btn btn-success']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var Photo $photo */
                $photo = $form->getData();

                if ($photo->getFile() instanceof UploadedFile) {
                    $mc = $this->get('smart_media')->getCollection($album->getGallery()->getMediaCollection()->getId());
                    $photo->setImageId($mc->upload($photo->getFile()));
                }

                $this->persist($photo, true);
                $this->addFlash('success', 'Photo uploaded successfully.');

                if ($album->getCoverImageId() == $album->getLastImageId()) {
                    $album->setCoverImageId($photo->getImageId());
                }

                $album
                    ->setPhotosCount($em->getRepository('GalleryModuleBundle:Photo')->countInAlbum($photo->getAlbum()))
                    ->setLastImageId($photo->getImageId())
                ;

                $this->persist($album, true);

                return $this->redirectToRoute('monolith_module.gallery.admin_album', [
                    'id'         => $album->getId(),
                    'gallery_id' => $album->getGallery()->getId(),
                ]);
            }
        }

        $albumPath  = null;
        foreach ($this->get('cms.node')->findByModule('GalleryModuleBundle') as $node) {
            if ($node->getParam('gallery_id') === (int) $id) {
                $albumPath = $this->generateUrl('monolith_module.gallery.album', [
                    '_folderPath' => $this->get('cms.folder')->getUri($node),
                    'id' => $id,
                ]);

                break;
            }
        }

        return $this->render('@GalleryModule/Admin/album.html.twig', [
            'form'      => $form->createView(),
            'photos'    => $em->getRepository(Photo::class)->findBy(['album' => $album], ['position' => 'DESC', 'id' => 'DESC']),
            'album'     => $album,
            'albumPath' => $albumPath,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param int $gallery_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function albumEditAction(Request $request, $id, $gallery_id): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $album = $em->find(Album::class, $id);

        if (empty($album) or $album->getGallery()->getId() != $gallery_id) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(AlbumFormType::class, $album);
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn btn-success']])
             ->add('delete', SubmitType::class, ['attr' => ['class' => 'btn btn-danger', 'onclick' => "return confirm('Вы уверены, что хотите удалить альбом?')"]])
             ->add('cancel', SubmitType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('delete')->isClicked()) {
                if ($album->getPhotosCount() > 0) {
                    $this->addFlash('error', 'Удалить можно только пустой альбом.');

                    return $this->redirectToRoute('monolith_module.gallery.admin_album_edit', ['id' => $album->getId(), 'gallery_id' => $gallery_id]);
                } else {
                    $this->addFlash('success', 'Album <b>'.$album.'</b> deleted successfully.');
                    $this->remove($album, true);
                }

                return $this->redirectToRoute('monolith_module.gallery.admin_gallery', ['id' => $gallery_id]);
            }

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('monolith_module.gallery.admin_gallery', ['id' => $gallery_id]);
            }

            if ($form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Album updated successfully.');

                return $this->redirectToRoute('monolith_module.gallery.admin_gallery', ['id' => $gallery_id]);
            }
        }

        return $this->render('@GalleryModule/Admin/album_edit.html.twig', [
            'form'  => $form->createView(),
            'album' => $album,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @param int $gallery_id
     * @param int $album_id
     * @param bool $set_as_cover
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function photoAction(Request $request, $id, $gallery_id, $album_id, $set_as_cover = false): Response
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $photo = $em->find(Photo::class, $id);

        if (empty($photo) or $photo->getAlbum()->getId() != $album_id or $photo->getAlbum()->getGallery()->getId() != $gallery_id) {
            throw $this->createNotFoundException();
        }

        $album = $photo->getAlbum();

        if ($set_as_cover) {
            $album->setCoverImageId($photo->getImageId());
            $this->persist($album, true);
            $this->addFlash('success', 'Photo set as cover successfully.');

            return $this->redirectToRoute('monolith_module.gallery.admin_photo', ['album_id' => $album_id, 'gallery_id' => $gallery_id, 'id' => $id]);
        }

        $form = $this->createForm(PhotoFormType::class, $photo);
        $form
            ->remove('file')
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn btn-success']])
            ->add('delete', SubmitType::class, ['attr' => ['class' => 'btn btn-danger', 'onclick' => "return confirm('Вы уверены, что хотите удалить фотографию?')"]])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']])
        ;

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var Photo $photo */
                $photo = $form->getData();

                if ($form->get('cancel')->isClicked()) {
                    return $this->redirectToRoute('monolith_module.gallery.admin_album', ['id' => $album_id, 'gallery_id' => $gallery_id]);
                }

                if ($form->get('delete')->isClicked()) {
                    $mc = $this->get('smart_media')->getCollection($album->getGallery()->getMediaCollection()->getId());
                    $mc->remove($photo->getImageId());

                    $this->remove($photo, true);
                    $this->addFlash('success', 'Photo deleted successfully.');

                    $album->setPhotosCount($em->getRepository(Photo::class)->countInAlbum($album));

                    if ($album->getCoverImageId() == $id) {
                        $lastPhoto = $em->getRepository(Photo::class)->findOneBy(['album' => $album], ['id' => 'DESC']);
                        $album->setCoverImageId(empty($lastPhoto) ? null : $lastPhoto->getImageId());
                    }

                    $this->persist($album, true);

                    return $this->redirectToRoute('monolith_module.gallery.admin_album', ['id' => $album_id, 'gallery_id' => $gallery_id]);
                }

                $this->persist($photo, true);
                $this->addFlash('success', 'Photo updated successfully.');

                return $this->redirectToRoute('monolith_module.gallery.admin_album', [
                    'id'         => $album_id,
                    'gallery_id' => $gallery_id,
                ]);
            }
        }

        return $this->render('@GalleryModule/Admin/photo.html.twig', [
            'form'  => $form->createView(),
            'photo' => $photo,
        ]);
    }
}
