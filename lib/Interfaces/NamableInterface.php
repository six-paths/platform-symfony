<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

interface NamableInterface
{
    public function getName(): string;
    public function setName(string $name): NamableInterface;
}
