<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoleApiType
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleApiType extends AclRoleType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', 'text', [
                'required' => true,
                'label'    => 'Role'
            ])
            ->add('appendUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ])
            ->add('removeUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $role = $event->getData();

            $role->setRole(strtoupper(trim(preg_replace('/[^\w\-]/i', '_', $role->getLabel()))));
        });

        $builder->get('appendUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->addRole($group);
            }
        });

        $builder->get('removeUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->removeRole($group);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => 'Oro\Bundle\UserBundle\Entity\Role',
            'intention'       => 'role',
            'csrf_protection' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'role';
    }
}
