<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\CoreBundle\Doctrine\ColumnTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="PhotoRepository")
 * @ORM\Table(name="gallery_photos")
 */
class Photo
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\FosUser;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $image_id;

    /**
     * @var Album
     *
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="photos", fetch="EXTRA_LAZY")
     */
    protected $album;

    /**
     * @var UploadedFile
     *
     * @Assert\File(
     *      maxSize = "10M",
     *      mimeTypes = {"image/jpeg", "image/png", "image/gif"}
     * )
     */
    protected $file;

    /**
     * Photo constructor.
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @param Album $album
     *
     * @return $this
     */
    public function setAlbum(Album $album): Photo
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return Album
     */
    public function getAlbum(): Album
    {
        return $this->album;
    }

    /**
     * @param UploadedFile $file
     *
     * @return $this
     */
    public function setFile(UploadedFile $file): Photo
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param int $image_id
     *
     * @return $this
     */
    public function setImageId(int $image_id): Photo
    {
        $this->image_id = $image_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getImageId(): int
    {
        return $this->image_id;
    }
}
