<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserApiType
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserApiType extends AbstractType
{
    /**
     * @var EventSubscriberInterface[]
     */
    protected $subscribers;

    /**
     * @param array $subscribers
     */
    public function __construct(array $subscribers = [])
    {
        $this->subscribers = $subscribers;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'      => false,
                'validation_groups'    => array('ProfileAPI', 'Default'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'pim_user_user';
    }
}
