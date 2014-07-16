<?php
namespace PhpEmber;

/**
 * Attribute type enumeration.
 */
final class AttributeType
{

    /**
     * Boolean type.
     */
    const BOOLEAN_TYPE = 'boolean';

    /**
     * Integer number.
     */
    const INTEGER_TYPE = 'integer';

    /**
     * Number with decimals.
     */
    const FLOAT_TYPE = 'float';

    /**
     * String.
     */
    const STRING_TYPE = 'string';

    /**
     * Date and time.
     * Valid values of this type are null or DateTime objects.
     */
    const DATE_TYPE = 'date';

    /**
     * Related object.
     * Values of this type are either strings or null, where a string is the
     * identifier of the related model, and null means that none is related.
     */
    const BELONGS_TO = 'belongsTo';

    /**
     * Related objects.
     * The value of this type is always an array or a Traversable object.
     */
    const HAS_MANY = 'hasMany';

}
