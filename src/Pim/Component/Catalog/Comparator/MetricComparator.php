<?php

namespace Pim\Component\Catalog\Comparator;

/**
 * Comparator which calculate change set for metrics
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return 'pim_catalog_metric' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function compare(array $data, array $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => []];
        $originals = array_merge($default, $originals);

        $diff = array_diff_assoc($data['value'], $originals['value']);

        if (!empty($diff)) {
            return $data;
        }

        return null;
    }
}