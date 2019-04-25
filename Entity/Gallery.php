<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use SmartCore\Bundle\MediaBundle\Entity\Collection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="galleries")
 */
class Gallery
{
    const ORDER_BY_CREATED       = 0;
    const ORDER_BY_POSITION_ASC  = 1;
    const ORDER_BY_POSITION_DESC = 2;
    const ORDER_BY_LAST_PHOTO    = 3; // @todo

    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Title;
    use ColumnTrait\FosUser;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $order_albums_by;

    /**
     * @var Album[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Album", mappedBy="gallery")
     */
    protected $albums;

    /**
     * @var Collection
     *
     * @ORM\ManyToOne(targetEntity="SmartCore\Bundle\MediaBundle\Entity\Collection")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $media_collection;

    /**
     * Gallery constructor.
     */
    public function __construct()
    {
        $this->albums           = new ArrayCollection();
        $this->created_at       = new \DateTime();
        $this->order_albums_by  = self::ORDER_BY_CREATED;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @param Album[]|ArrayCollection $albums
     *
     * @return $this
     */
    public function setAlbums($albums): Gallery
    {
        $this->albums = $albums;

        return $this;
    }

    /**
     * @return Album[]|ArrayCollection
     */
    public function getAlbums(): PersistentCollection
    {
        return $this->albums;
    }

    /**
     * @param Collection $media_collection
     *
     * @return $this
     */
    public function setMediaCollection(Collection $media_collection): Gallery
    {
        $this->media_collection = $media_collection;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMediaCollection(): ?Collection
    {
        return $this->media_collection;
    }

    /**
     * @return int
     */
    public function getOrderAlbumsBy(): int
    {
        return $this->order_albums_by;
    }

    /**
     * @param int $order_albums_by
     *
     * @return $this
     */
    public function setOrderAlbumsBy(int $order_albums_by): Gallery
    {
        $this->order_albums_by = $order_albums_by;

        return $this;
    }
}
