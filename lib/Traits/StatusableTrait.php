<?php

namespace Sixpaths\Platform\Symfony\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sixpaths\Platform\Symfony\Interfaces\StatusableInterface;

trait StatusableTrait
{
    /**
     * @ORM\Column(type = "integer", name = "status", nullable = false)
     * @Gedmo\Versioned
     *
     * @var integer
     */
    private $status;

    /**
     * Gets the value of status.
     *
     * @return integer
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param integer $status
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\StatusableInterface
     */
    public function setStatus(int $status): StatusableInterface
    {
        $this->status = $status;

        return $this;
    }
}
