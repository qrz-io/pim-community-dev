<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Component\Localization\LocaleConfigurationInterface;
use Pim\Component\Localization\Localizer\AbstractNumberLocalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transforms normalized values of ProductTemplate into product value objects prior to binding to the form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformProductTemplateValuesSubscriber implements EventSubscriberInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var LocaleConfigurationInterface */
    protected $localeConfiguration;

    /**
     * @param NormalizerInterface          $normalizer
     * @param DenormalizerInterface        $denormalizer
     * @param TokenStorageInterface        $tokenStorage
     * @param LocaleConfigurationInterface $localeConfiguration
     */
    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        TokenStorageInterface $tokenStorage,
        LocaleConfigurationInterface $localeConfiguration
    ) {
        $this->normalizer          = $normalizer;
        $this->denormalizer        = $denormalizer;
        $this->tokenStorage        = $tokenStorage;
        $this->localeConfiguration = $localeConfiguration;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT  => 'postSubmit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || !$data instanceof ProductTemplateInterface) {
            return;
        }

        $user = $this->getUser();
        $context['decimal_separator'] = $this->localeConfiguration->getDecimalSeparator($user->getUiLocale());
        $values = $this->denormalizer->denormalize($data->getValuesData(), 'ProductValue[]', 'json', $context);
        $data->setValues($values);
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (null === $data || !$data instanceof ProductTemplateInterface) {
            return;
        }

        $user = $this->getUser();
        $valuesData = $this->normalizer->normalize($data->getValues(), 'json', [
            'entity'            => 'product',
            'decimal_separator' => $this->localeConfiguration->getDecimalSeparator($user->getUiLocale())
        ]);
        $data->setValuesData($valuesData);
    }

    /**
     * Get user
     *
     * @return mixed|null
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (null === $user) {
            return null;
        }

        return $user;
    }
}
