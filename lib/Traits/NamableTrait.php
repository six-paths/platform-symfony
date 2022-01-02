<?php

namespace Sixpaths\Platform\Symfony\Security\Validator\Traits;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

trait NamableTrait
{
    /**
     * @Serializer\Type("string")
     * @Assert\Length(min = 2, max = 60)
     * @Assert\NotBlank
     *
     * @var string
     */
    public $name;

    /**
     * Gets the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
