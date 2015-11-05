<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Bundle\LocalizationBundle\Validator\Constraints\IsNumber;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNumberLocalizer implements LocalizerInterface
{
    /** @var array */
    protected $attributeTypes;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     * @param array              $attributeTypes
     */
    public function __construct(ValidatorInterface $validator, array $attributeTypes)
    {
        $this->validator      = $validator;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return $number;
        }

        if (isset($options['decimal_separator'])) {
            $matchesNumber = $this->getMatchesNumber($number);
            if (!isset($matchesNumber['decimal'])) {
                return $number;
            }

            return str_replace(static::DEFAULT_DECIMAL_SEPARATOR, $options['decimal_separator'], $number);
        }

        if (isset($options['locale'])) {
            $numberFormatter = new \NumberFormatter($options['locale'], \NumberFormatter::DECIMAL);

            if (floor($number) != $number) {
                $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 4);
            }

            return $numberFormatter->format($number);
        }

        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return $number;
        }

        $matchesNumber = $this->getMatchesNumber($number);
        if (!isset($matchesNumber['decimal'])) {
            return $number;
        }

        return str_replace($matchesNumber['decimal'], static::DEFAULT_DECIMAL_SEPARATOR, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($number, array $options = [], $attributeCode)
    {
        if (null === $number || ''  === $number) {
            return null;
        }

        if (isset($options['locale'])) {
            $numberFormatter = new \NumberFormatter($options['locale'], \NumberFormatter::DECIMAL);
            $options['decimal_separator'] = $numberFormatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        }

        $options = $this->checkOptions($options);

        $numberValidator = new IsNumber(
            [
                'decimalSeparator' => $options['decimal_separator'],
                'path'             => $attributeCode
            ]
        );

        return $this->validator->validate($number, $numberValidator);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }

    /**
     * @param string $number
     *
     * @return array
     */
    protected function getMatchesNumber($number)
    {
        preg_match('|\d+((?P<decimal>\D+)\d+)?|', $number, $matches);

        return $matches;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function checkOptions(array $options)
    {
        if (!isset($options['decimal_separator']) || '' === $options['decimal_separator']) {
            $options['decimal_separator'] = static::DEFAULT_DECIMAL_SEPARATOR;
        }

        return $options;
    }
}
