<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

interface ActivatableInterface
{
    public function isActive(): bool;
    public function getIsActive(): bool;
    public function setIsActive(bool $isActive): ActivatableInterface;
}
