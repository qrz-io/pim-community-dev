<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for group
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupHandler implements HandlerInterface
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LocalizedAttributeConverterInterface */
    protected $localizedConverter;

    /**
     * Constructor for handler
     *
     * @param FormInterface                        $form
     * @param Request                              $request
     * @param SaverInterface                       $groupSaver
     * @param ProductRepositoryInterface           $productRepository
     * @param LocalizedAttributeConverterInterface $localizedConverter
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        SaverInterface $groupSaver,
        ProductRepositoryInterface $productRepository,
        LocalizedAttributeConverterInterface $localizedConverter = null
    ) {
        $this->form               = $form;
        $this->request            = $request;
        $this->groupSaver         = $groupSaver;
        $this->productRepository  = $productRepository;
        $this->localizedConverter = $localizedConverter;
    }

    /**
     * @param array $form
     *
     * @return array
     */
    protected function convertLocalizedValues(array $form)
    {
        $form['productTemplate']['values'] = $this->localizedConverter
            ->convertForm($form['productTemplate']['values'], ['locale' => 'fr']);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function process($group)
    {
        $this->form->setData($group);

        if ($this->request->isMethod('POST')) {
            // TODO : how to fix this ? Load products when ODM storage is used to enable validation
            if (null === $group->getProducts()) {
                $products = $this->productRepository->findAllForGroup($group)->toArray();
                $group->setProducts($products);
            }

            if ($group->getType()->isVariant()) {
                if (null !== $this->request->request) {
                    $oldForm = $this->request->request->get('pim_enrich_variant_group_form');
                    $form = $this->convertLocalizedValues($oldForm);
                    $this->request->request->set('pim_enrich_variant_group_form', $form);
                    $violations = $this->localizedConverter->getViolations();
                }
            }

            $this->form->submit($this->request);
/*
            $productTemplate = $this->form->get('productTemplate');
            foreach ($violations as $violation) {
                $productTemplate->addError(new FormError('es', 'template', [], null, $violation));
            }

            $this->form->offsetSet('productTemplate', $productTemplate);
*/
            if ($this->form->isValid() && ((isset($violations) && 0 === $violations->count()) || !isset($violations))) {
                $this->onSuccess($group);

                return true;
            } elseif ($group->getType()->isVariant() && $group->getId()) {
                $products = $this->productRepository->findAllForVariantGroup($group);
                $group->setProducts($products);
            }
        }

        return false;
    }

    /**
     * Call when form is valid
     *
     * @param GroupInterface $group
     */
    protected function onSuccess(GroupInterface $group)
    {
        $appendProducts = $this->form->get('appendProducts')->getData();
        $removeProducts = $this->form->get('removeProducts')->getData();
        $options = [
            'add_products'    => $appendProducts,
            'remove_products' => $removeProducts
        ];
        if ($group->getType()->isVariant()) {
            $options['copy_values_to_products'] = true;
        }
        $this->groupSaver->save($group, $options);
    }
}
