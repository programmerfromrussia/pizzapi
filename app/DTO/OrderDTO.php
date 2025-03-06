<?php

namespace App\DTO;

class OrderDTO
{
    public string $address;
    public string $city;
    public string $country;

    public function __construct(array $data)
    {
        $this->address = $data['address'];
        $this->city = $data['city'];
        $this->country = $data['country'];
    }
}
