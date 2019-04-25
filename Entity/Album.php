<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Smart\CoreBundle\Doctrine\ColumnTrait;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="gallery_albums")
 */
class Album
{
    use ColumnTrait\Id;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\Title;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\FosUser;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $cover_image_id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $last_image_id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $photos_count;

    /**
     * @var Gallery
     *
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="albums")
     */
    protected $gallery;

    /**
     * @var Photo[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Photo", mappedBy="album")
     */
    protected $photos;

    /**
     * Album constructor.
     */
    public function __construct()
    {
        $this->created_at   = new \DateTime();
        $this->updated_at   = new \DateTime();
        $this->photos_count = 0;
        $this->position     = 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @param int $cover_image_id
     *
     * @return $this
     */
    public function setCoverImageId(?int $cover_image_id): Album
    {
        $this->cover_image_id = $cover_image_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCoverImageId(): ?int
    {
        return $this->cover_image_id;
    }

    /**
     * @param Gallery $gallery
     *
     * @return $this
     */
    public function setGallery(Gallery $gallery): Album
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * @return Gallery
     */
    public function getGallery(): Gallery
    {
        return $this->gallery;
    }

    /**
     * @param Photo[]|ArrayCollection $photos
     *
     * @return $this
     */
    public function setPhotos($photos): Album
    {
        $this->photos = $photos;

        return $this;
    }

    /**
     * @return Photo[]|ArrayCollection
     */
    public function getPhotos(): PersistentCollection
    {
        return $this->photos;
    }

    /**
     * @param int $photos_count
     *
     * @return $this
     */
    public function setPhotosCount(int $photos_count): Album
    {
        $this->photos_count = $photos_count;

        return $this;
    }

    /**
     * @return int
     */
    public function getPhotosCount(): int
    {
        return $this->photos_count;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function lastUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @param int $last_image_id
     *
     * @return $this
     */
    public function setLastImageId(?int $last_image_id): Album
    {
        $this->last_image_id = $last_image_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getLastImageId(): ?int
    {
        return $this->last_image_id;
    }
}
