<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

interface StatusableInterface
{
    public function getStatus(): int;
    public function setStatus(int $status): StatusableInterface;
}
