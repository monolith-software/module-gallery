<?php

declare(strict_types=1);

namespace Monolith\Module\Gallery\Entity;

use Doctrine\ORM\EntityRepository;

class PhotoRepository extends EntityRepository
{
    /**
     * @param Album|int $album
     *
     * @return int
     */
    public function countInAlbum($album): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.album = :album')
            ->setParameter('album', $album)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
