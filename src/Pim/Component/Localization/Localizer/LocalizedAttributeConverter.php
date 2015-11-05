<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Convert localized attributes to default format
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizedAttributeConverter implements LocalizedAttributeConverterInterface
{
    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ConstraintViolationListInterface */
    protected $violations;

    /**
     * @param LocalizerRegistryInterface   $localizerRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        LocalizerRegistryInterface $localizerRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->localizerRegistry   = $localizerRegistry;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $items, array $options = [])
    {
        $this->violations = new ConstraintViolationList();
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($items));

        foreach ($items as $code => $item) {
            $path = sprintf('values[%s]', $code);
            if (isset($attributeTypes[$code])) {
                $localizer = $this->localizerRegistry->getLocalizer($attributeTypes[$code]);

                if (null !== $localizer) {
                    foreach ($item as $index => $data) {
                        $items[$code][$index] = $this->convertAttribute($localizer, $data, $options, $path);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * Convert a localized attribute
     *
     * @param LocalizerInterface $localizer
     * @param array              $item
     * @param array              $options
     * @param string             $path
     *
     * @return array
     */
    protected function convertAttribute(LocalizerInterface $localizer, array $item, array $options, $path)
    {
        $violations = $localizer->validate($item['data'], $options, $path);
        if (null !== $violations) {
            $this->violations->addAll($violations);
        }

        $item['data'] = $localizer->delocalize($item['data'], $options);

        return $item;
    }
}
