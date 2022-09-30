<?php declare(strict_types=1);

namespace GraphQL\Type\Definition;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\Node;
use GraphQL\Utils\Utils;
use function floatval;
use function intval;
use function is_array;
use function is_bool;
use function is_numeric;
use function sprintf;

class IntType extends ScalarType
{
    // As per the GraphQL Spec, Integers are only treated as valid when a valid
    // 32-bit signed integer, providing the broadest support across platforms.
    //
    // n.b. JavaScript's integers are safe between -(2^53 - 1) and 2^53 - 1 because
    // they are internally represented as IEEE 754 doubles.
    public const MAX_INT = 2147483647;
    public const MIN_INT = -2147483648;

    public string $name = Type::INT;

    public ?string $description
        = 'The `Int` scalar type represents non-fractional signed whole numeric
values. Int can represent values between -(2^31) and 2^31 - 1. ';

    /**
     * @param mixed $value
     *
     * @return int|null
     *
     * @throws Error
     */
    public function serialize($value)
    {
        return $this->coerceInt($value);
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    private function coerceInt($value)
    {
        if (is_array($value)) {
            throw new Error(
                sprintf('Int cannot represent an array value: %s', Utils::printSafe($value))
            );
        }

        if ($value === '') {
            throw new Error(
                'Int cannot represent non-integer value: (empty string)'
            );
        }

        if (! is_numeric($value) && ! is_bool($value)) {
            throw new Error(
                'Int cannot represent non-integer value: ' .
                Utils::printSafe($value)
            );
        }

        $num = floatval($value);
        if ($num > self::MAX_INT || $num < self::MIN_INT) {
            throw new Error(
                'Int cannot represent non 32-bit signed integer value: ' .
                Utils::printSafe($value)
            );
        }
        $int = intval($num);
        // int cast with == used for performance reasons
        // phpcs:ignore
        if ($int != $num) {
            throw new Error(
                'Int cannot represent non-integer value: ' .
                Utils::printSafe($value)
            );
        }

        return $int;
    }

    /**
     * @param mixed $value
     *
     * @return int|null
     *
     * @throws Error
     */
    public function parseValue($value)
    {
        return $this->coerceInt($value);
    }

    /**
     * @param Node         $valueNode
     * @param mixed[]|null $variables
     *
     * @return int|null
     *
     * @throws Exception
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof IntValueNode) {
            $val = (int) $valueNode->value;
            if ($valueNode->value === (string) $val && self::MIN_INT <= $val && $val <= self::MAX_INT) {
                return $val;
            }
        }

        // Intentionally without message, as all information already in wrapped Exception
        throw new Exception();
    }
}
