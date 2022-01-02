<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

use Doctrine\Common\Collections\Collection;
use Sixpaths\Platform\Symfony\Interfaces\IdentifiableInterface;

interface AbstractEntityInterface extends
    IdentifiableInterface
{
    public function hasCollectionEntityByCriteria(AbstractEntityInterface $entity, Collection $collection, string $criteriaFieldName): bool;
    public function getCollectionEntityByCriteria(AbstractEntityInterface $entity, Collection $collection, string $criteriaFieldName): ?AbstractEntityInterface;
}
