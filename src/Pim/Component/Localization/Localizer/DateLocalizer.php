<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Bundle\LocalizationBundle\Validator\Constraints\Date;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Check if date provided respects the format expected and convert it
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateLocalizer implements LocalizerInterface
{
    /** @var array */
    protected $attributeTypes;

    /** @var FormatProviderInterface */
    protected $formatProvider;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ValidatorInterface      $validator
     * @param FormatProviderInterface $formatProvider
     * @param array                   $attributeTypes
     */
    public function __construct(
        ValidatorInterface $validator,
        FormatProviderInterface $formatProvider,
        array $attributeTypes
    ) {
        $this->formatProvider = $formatProvider;
        $this->attributeTypes = $attributeTypes;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($date, array $options = [], $attributeCode)
    {
        if (null === $date || '' === $date) {
            return null;
        }

        $options = $this->checkOptions($options);

        $dateValidator = new Date(
            [
                'dateFormat' => $options['date_format'],
                'path'       => $attributeCode
            ]
        );

        return $this->validator->validate($date, $dateValidator);
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        if (isset($options['date_format'])) {
            $datetime = $this->getDateTime($date, $options);

            return $datetime->format(static::DEFAULT_DATE_FORMAT);
        }

        if (isset($options['locale'])) {
            return $date; // @TODO: not yet implemented (PIM-5146)
        }
    }

    /**
     * {@inheritdoc}
     */
    public function localize($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        if (isset($options['date_format'])) {
            $datetime = new \DateTime();
            $datetime = $datetime->createFromFormat(static::DEFAULT_DATE_FORMAT, $date);

            return $datetime->format($options['date_format']);
        }

        if (isset($options['locale'])) {
            $format = $this->formatProvider->getFormat($options['locale']);

            $datetime = new \DateTime();
            $datetime = $datetime->createFromFormat(static::DEFAULT_DATE_FORMAT, $date);

            return $datetime->format($format);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }

    /**
     * Get a \DateTime from date and format date provided
     *
     * @param string $date
     * @param array  $options
     *
     * @return \DateTime|false
     */
    protected function getDateTime($date, array $options)
    {
        $datetime = new \DateTime();

        return $datetime->createFromFormat($options['date_format'], $date);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function checkOptions(array $options)
    {
        if (!isset($options['date_format']) || '' === $options['date_format']) {
            $options['date_format'] = static::DEFAULT_DATE_FORMAT;
        }

        return $options;
    }

}
