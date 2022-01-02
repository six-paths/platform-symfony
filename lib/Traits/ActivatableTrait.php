<?php

namespace Sixpaths\Platform\Symfony\Traits;

use Gedmo\Mapping\Annotation as Gedmo;
use Sixpaths\Platform\Symfony\Interfaces\ActivatableInterface;

trait ActivatableTrait
{
    /**
     * @ORM\Column(name = "is_active", type = "boolean")
     * @Gedmo\Versioned
     *
     * @var bool
     */
    private $isActive = true;

    /**
     * Gets the value of isActive.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Gets the value of isActive.
     *
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Sets the value of isActive.
     *
     * @param bool $isActive
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\ActivatableInterface
     */
    public function setIsActive(bool $isActive): ActivatableInterface
    {
        $this->isActive = $isActive;

        return $this;
    }
}
