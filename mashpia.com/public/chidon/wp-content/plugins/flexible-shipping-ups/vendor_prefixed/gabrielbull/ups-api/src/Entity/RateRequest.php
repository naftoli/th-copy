<?php

namespace UpsFreeVendor\Ups\Entity;

class RateRequest
{
    /** @deprecated */
    public $PickupType;
    /** @deprecated */
    public $Shipment;
    /**
     * @var PickupType
     */
    private $pickupType;
    /**
     * @var CustomerClassification
     */
    private $customerClassification;
    /**
     * @var Shipment
     */
    private $shipment;
    public function __construct()
    {
        $this->setShipment(new \UpsFreeVendor\Ups\Entity\Shipment());
        $this->setPickupType(new \UpsFreeVendor\Ups\Entity\PickupType());
    }
    /**
     * @return PickupType
     */
    public function getPickupType()
    {
        return $this->pickupType;
    }
    /**
     * @param PickupType $pickupType
     *
     * @return $this
     */
    public function setPickupType(\UpsFreeVendor\Ups\Entity\PickupType $pickupType)
    {
        $this->PickupType = $pickupType;
        $this->pickupType = $pickupType;
        return $this;
    }
    /**
     * @return CustomerClassification
     */
    public function getCustomerClassification()
    {
        return $this->customerClassification;
    }
    /**
     * @param CustomerClassification $customerClassification
     *
     * @return $this
     */
    public function setCustomerClassification(\UpsFreeVendor\Ups\Entity\CustomerClassification $customerClassification)
    {
        $this->customerClassification = $customerClassification;
        return $this;
    }
    /**
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }
    /**
     * @param Shipment $shipment
     *
     * @return $this
     */
    public function setShipment(\UpsFreeVendor\Ups\Entity\Shipment $shipment)
    {
        $this->Shipment = $shipment;
        $this->shipment = $shipment;
        return $this;
    }
}
