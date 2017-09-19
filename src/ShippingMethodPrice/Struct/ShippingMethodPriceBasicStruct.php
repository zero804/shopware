<?php declare(strict_types=1);

namespace Shopware\ShippingMethodPrice\Struct;

use Shopware\Framework\Struct\Struct;

class ShippingMethodPriceBasicStruct extends Struct
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $shippingMethodUuid;

    /**
     * @var float
     */
    protected $quantityFrom;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var float
     */
    protected $factor;

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getShippingMethodUuid(): string
    {
        return $this->shippingMethodUuid;
    }

    public function setShippingMethodUuid(string $shippingMethodUuid): void
    {
        $this->shippingMethodUuid = $shippingMethodUuid;
    }

    public function getQuantityFrom(): float
    {
        return $this->quantityFrom;
    }

    public function setQuantityFrom(float $quantityFrom): void
    {
        $this->quantityFrom = $quantityFrom;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getFactor(): float
    {
        return $this->factor;
    }

    public function setFactor(float $factor): void
    {
        $this->factor = $factor;
    }
}
