<?php

namespace Sixpaths\Platform\Symfony\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface;
use Sixpaths\Platform\Symfony\Traits\IdentifiableTrait;

/**
 * @Gedmo\SoftDeleteable(fieldName = "deletedAt")
 */
abstract class AbstractEntity implements AbstractEntityInterface
{
    use IdentifiableTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(name = "deleted_at", type = "datetime", nullable = true)
     *
     * @var \DateTime|null
     */
    private $deletedAt;

    /**
     * @return \DateTime|null
     */
    final public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime|null $deletedAt
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface
     */
    final public function setDeletedAt(?\DateTime $deletedAt): AbstractEntityInterface
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Adds an item to a collection.
     *
     * @param \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface $entity
     * @param \Doctrine\Common\Collections\Collection $collection
     *
     * @return void
     */
    final protected function addToCollection(
        AbstractEntityInterface $entity,
        Collection $collection
    ): void {
        if ($collection->contains($entity) === false) {
            $collection->add($entity);
        }

        return;
    }

    /**
     * Removes an item from a collection.
     *
     * @param \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface $entity
     * @param \Doctrine\Common\Collections\Collection $collection
     *
     * @return void
     */
    final protected function removeFromCollection(
        AbstractEntityInterface $entity,
        Collection $collection
    ): void {
        if ($collection->contains($entity) === true) {
            $collection->removeElement($entity);
        }

        return;
    }

    /**
     * Determines whether an entity matching the criteria exists on the collection.
     *
     * @param \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface $entity
     * @param \Doctrine\Common\Collections\Collection $collection
     * @param string $criteriaFieldName
     *
     * @return boolean
     */
    final public function hasCollectionEntityByCriteria(
        AbstractEntityInterface $entity,
        Collection $collection,
        string $criteriaFieldName
    ): bool {
        $matchedResults = $this->getCollectionEntityByCriteria($entity, $collection, $criteriaFieldName);

        return $matchedResults instanceof AbstractEntityInterface;
    }

    /**
     * Gets an entity from the collection by matching criteria.
     *
     * @param \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface $entity
     * @param \Doctrine\Common\Collections\Collection $collection
     * @param string $criteriaFieldName
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface|null
     */
    final public function getCollectionEntityByCriteria(
        AbstractEntityInterface $entity,
        Collection $collection,
        string $criteriaFieldName
    ): ?AbstractEntityInterface {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq($criteriaFieldName, $entity))
            ->setMaxResults(1);

        return $collection->matching($criteria)->first() ?: null;
    }

    /**
     * Gets an entity from the collection by matching criteria value.
     *
     * @param \Doctrine\Common\Collections\Collection $collection
     * @param string $criteriaFieldName
     * @param mixed $value
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface|null
     */
    final public function getCollectionEntityByCriteriaValue(
        Collection $collection,
        string $criteriaFieldName,
        $value
    ): ?AbstractEntityInterface {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq($criteriaFieldName, $value))
            ->setMaxResults(1);

        return $collection->matching($criteria)->first() ?: null;
    }
}
