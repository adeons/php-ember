<?php
namespace PhpEmber\Transforms;

class Date implements \PhpEmber\TransformInterface
{

    /**
     *
     * @var string
     */
    private $valueFormat;

    /**
     *
     * @var string
     */
    private $payloadFormat;

    /**
     *
     * @param string $format
     */
    public function __construct($valueFormat = null, $payloadFormat = DATE_ISO8601)
    {
        $this->valueFormat = $valueFormat;
        $this->payloadFormat = $payloadFormat;
    }

    /**
     *
     * @return string
     */
    public function getValueFormat()
    {
        return $this->valueFormat;
    }

    /**
     *
     * @return string
     */
    public function getPayloadFormat()
    {
        return $this->payloadFormat;
    }

    public function serialize($value, $options = array())
    {
        if (!$value) {
            // empty value is resolved as null
            return null;
        }

        if (is_object($value)) {

            // DateTime instance
            $date = $value;

        } else {

            // cast string as DateTime
            $date = \DateTime::createFromFormat($this->valueFormat, $value);

            if (!$date) {

                throw new \InvalidArgumentException(sprintf(
                    'Could not parse "%s" as date.', $value));
            }
        }

        return $date->format($this->payloadFormat);
    }

}
