<?php

namespace Sixpaths\Platform\Symfony\Traits;

use Doctrine\ORM\Mapping as ORM;

trait IdentifiableTrait
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy = "UUID")
     * @ORM\Column(type = "guid")
     *
     * @var string
     */
    protected $id;

    public function getId(): string
    {
        return $this->id;
    }
}
