<?php

namespace Sixpaths\Platform\Symfony\Interfaces;

interface AddressableInterface
{
    public function getAddressLine1(): ?string;
    public function setAddressLine1(string $addressLine1 = null): AddressableInterface;
    public function getAddressLine2(): ?string;
    public function setAddressLine2(string $addressLine2 = null): AddressableInterface;
    public function getAddressLine3(): ?string;
    public function setAddressLine3(string $addressLine3 = null): AddressableInterface;
    public function getTown(): ?string;
    public function setTown(string $town = null): AddressableInterface;
    public function getCounty(): ?string;
    public function setCounty(string $county = null): AddressableInterface;
    public function getCountry(): ?string;
    public function setCountry(string $country = null): AddressableInterface;
    public function getPostcode(): ?string;
    public function setPostcode(string $postcode = null): AddressableInterface;
}
