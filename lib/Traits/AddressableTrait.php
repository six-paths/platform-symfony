<?php

namespace Sixpaths\Platform\Symfony\Traits;

use Gedmo\Mapping\Annotation as Gedmo;
use Sixpaths\Platform\Symfony\Interfaces\AddressableInterface;

trait AddressableTrait
{
    /**
     * @ORM\Column(name = "address_line_1", type = "string", length = 100, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $addressLine1;

    /**
     * @ORM\Column(name = "address_line_2", type = "string", length = 100, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $addressLine2;

    /**
     * @ORM\Column(name = "address_line_3", type = "string", length = 100, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $addressLine3;

    /**
     * @ORM\Column(name = "town", type = "string", length = 60, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $town;

    /**
     * @ORM\Column(name = "county", type = "string", length = 60, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $county;

    /**
     * @ORM\Column(name = "country", type = "string", length = 60, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $country;

    /**
     * @ORM\Column(name = "postcode", type = "string", length = 8, nullable = true)
     * @Gedmo\Versioned
     *
     * @var string|null
     */
    private $postcode;

    /**
     * Gets the value of addressLine1.
     *
     * @return string|null
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * Sets the value of addressLine1.
     *
     * @param string|null $addressLine1
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setAddressLine1(string $addressLine1 = null): AddressableInterface
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * Gets the value of addressLine2.
     *
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * Sets the value of addressLine2.
     *
     * @param string|null $addressLine2
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setAddressLine2(string $addressLine2 = null): AddressableInterface
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * Gets the value of addressLine3.
     *
     * @return string|null
     */
    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    /**
     * Sets the value of addressLine3.
     *
     * @param string|null $addressLine3
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setAddressLine3(string $addressLine3 = null): AddressableInterface
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * Gets the value of town.
     *
     * @return string|null
     */
    public function getTown(): ?string
    {
        return $this->town;
    }

    /**
     * Sets the value of town.
     *
     * @param string|null $town
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setTown(string $town = null): AddressableInterface
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Gets the value of county.
     *
     * @return string|null
     */
    public function getCounty(): ?string
    {
        return $this->county;
    }

    /**
     * Sets the value of county.
     *
     * @param string|null $county
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setCounty(string $county = null): AddressableInterface
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Gets the value of country.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Sets the value of country.
     *
     * @param string|null $country
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setCountry(string $country = null): AddressableInterface
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Gets the value of postcode.
     *
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * Sets the value of postcode.
     *
     * @param string|null $postcode
     *
     * @return \Sixpaths\Platform\Symfony\Interfaces\AddressableInterface
     */
    public function setPostcode(string $postcode = null): AddressableInterface
    {
        $this->postcode = $postcode;

        return $this;
    }
}
