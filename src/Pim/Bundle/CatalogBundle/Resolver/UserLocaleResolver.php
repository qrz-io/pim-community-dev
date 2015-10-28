<?php

namespace Pim\Bundle\CatalogBundle\Resolver;

use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserLocaleResolver
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var FormatProviderInterface */
    protected $dateFormatProvider;

    /** @var FormatProviderInterface */
    protected $numberFormatProvider;

    /**
     * @param RequestStack            $requestStack
     * @param FormatProviderInterface $dateFormatProvider
     * @param FormatProviderInterface $numberFormatProvider
     */
    public function __construct(
        RequestStack $requestStack,
        FormatProviderInterface $dateFormatProvider,
        FormatProviderInterface $numberFormatProvider
    ) {
        $this->requestStack         = $requestStack;
        $this->dateFormatProvider   = $dateFormatProvider;
        $this->numberFormatProvider = $numberFormatProvider;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $locale = $this->getLocale();

        return [
            'decimal_separator' => $this->numberFormatProvider->getFormat($locale)['decimal_separator'],
            'date_format'       => $this->dateFormatProvider->getFormat($locale),
        ];
    }

    /**
     * Get current user
     *
     * @return UserInterface|null
     */
    protected function getLocale()
    {
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            return 'en';
        }

        return $request->getLocale();
    }
}
