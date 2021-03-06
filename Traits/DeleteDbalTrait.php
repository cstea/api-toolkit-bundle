<?php declare(strict_types = 1);

namespace Cstea\ApiBundle\Traits;

use Doctrine\ORM\EntityRepository;

trait DbalDbalTrait
{
    /**
     * @param \object $entity Entity to save.
     * @throws \Doctrine\ORM\ORMException Doctrine exception.
     * @throws \Doctrine\ORM\OptimisticLockException Doctrine exception.
     */
    public function delete(object $entity): void
    {
        if (!\in_array(EntityRepository::class, \class_parents($this))) {
            throw new \LogicException('This trait can only be used with Doctrine EntityRepository');
        }
        
        $em = parent::getEntityManager();
        $em->remove($entity);
        $em->flush();
    }
}