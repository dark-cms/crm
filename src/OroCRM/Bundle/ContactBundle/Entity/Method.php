<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_contact_method")
 */
class Method
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32)
     * @ORM\Id
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, unique=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $label;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address type label
     *
     * @param string $label
     * @return Source
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get address type label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }
}
