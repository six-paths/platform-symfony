<?php

namespace Sixpaths\Platform\Symfony\Response;

use JMS\Serializer\Annotation as Serializer;
use Sixpaths\Platform\Symfony\Entity\AbstractEntity;
use Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface;
use Sixpaths\Platform\Symfony\Interfaces\ActivatableInterface;
use Sixpaths\Platform\Symfony\Interfaces\AddressableInterface;
use Sixpaths\Platform\Symfony\Interfaces\IdentifiableInterface;
use Sixpaths\Platform\Symfony\Interfaces\NamableInterface;
use Sixpaths\Platform\Symfony\Interfaces\ResponseInterface;
use Sixpaths\Platform\Symfony\Interfaces\RoleableInterface;
use Sixpaths\Platform\Symfony\Interfaces\StatusableInterface;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * @Serializer\Exclude
     *
     * @var \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface|null
     */
    protected $entity;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var string
     */
    protected $id;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var string
     */
    protected $name;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var array<int, string>
     */
    protected $roles;

    /**
     * @Serializer\Groups({"default"})
     *
     * @var integer
     */
    protected $status;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var boolean
     */
    protected $isActive;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $addressLine1;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $addressLine2;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $addressLine3;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $town;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $county;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $country;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string|null
     */
    protected $postcode;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @Serializer\Groups({"list", "details"})
     *
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct(
        AbstractEntityInterface $entity = null,
        bool $includeTraitValues = true
    ) {
        $this->entity = $entity;

        if ($includeTraitValues) {
            $this->addTraitValues();
        }
    }

    /**
     * Gets the value of entity
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AbstractEntityInterface|null
     */
    final public function getEntity(): ?AbstractEntityInterface
    {
        return $this->entity;
    }

    private function addTraitValues(): void
    {
        $this->addActivatableTraitValues();
        $this->addAddressableTraitValues();
        $this->addIdentifiableTraitValues();
        $this->addNamableTraitValues();
        $this->addRoleableTraitValues();
        $this->addStatusableTraitValues();
        $this->addTimestampableTraitValues();
    }

    private function addActivatableTraitValues(): void
    {
        if ($this->entity instanceof ActivatableInterface) {
            $this->isActive = $this->entity->getIsActive();
        }
    }

    private function addAddressableTraitValues(): void
    {
        if ($this->entity instanceof AddressableInterface) {
            $this->addressLine1 = $this->entity->getAddressLine1();
            $this->addressLine2 = $this->entity->getAddressLine2();
            $this->addressLine3 = $this->entity->getAddressLine3();
            $this->town = $this->entity->getTown();
            $this->county = $this->entity->getCounty();
            $this->country = $this->entity->getCountry();
            $this->postcode = $this->entity->getPostcode();
        }
    }

    private function addIdentifiableTraitValues(): void
    {
        if ($this->entity instanceof IdentifiableInterface) {
            $this->id = $this->entity->getId();
        }
    }

    private function addNamableTraitValues(): void
    {
        if ($this->entity instanceof NamableInterface) {
            $this->name = $this->entity->getName();
        }
    }

    private function addRoleableTraitValues(): void
    {
        if ($this->entity instanceof RoleableInterface) {
            $this->roles = $this->entity->getRoles();
        }
    }

    private function addStatusableTraitValues(): void
    {
        if ($this->entity instanceof StatusableInterface) {
            $this->status = $this->entity->getStatus();
        }
    }

    private function addTimestampableTraitValues(): void
    {
        if (is_callable([$this->entity, 'getCreatedAt'])) {
            $this->createdAt = $this->entity->getCreatedAt();
        }

        if (is_callable([$this->entity, 'getUpdatedAt'])) {
            $this->updatedAt = $this->entity->getUpdatedAt();
        }
    }
}
