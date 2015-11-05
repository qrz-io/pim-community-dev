<?php

namespace Pim\Component\Localization\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product values with a localized format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $valuesNormalizer;

    /** @var LocalizerInterface */
    protected $localizer;

    /**
     * @param NormalizerInterface $valuesNormalizer
     * @param LocalizerInterface  $localizer
     */
    public function __construct(NormalizerInterface $valuesNormalizer, LocalizerInterface $localizer)
    {
        $this->valuesNormalizer = $valuesNormalizer;
        $this->localizer        = $localizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        $result = $this->valuesNormalizer->normalize($entity, $format, $context);

        if (AttributeTypes::NUMBER === $entity->getAttribute()->getAttributeType()) {
            $result['data'] = $this->localizer->localize($result['data'], $context);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }
}
