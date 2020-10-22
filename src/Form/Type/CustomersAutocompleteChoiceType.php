<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomersAutocompleteChoiceType extends AbstractType {
    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'choice_name' => 'lastName',
            'choice_value' => 'id',
            'multiple' => true,
            'resource' => 'sylius.customer',
        ]);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void {
        $view->vars['remote_criteria_name'] = 'phrase';
        $view->vars['remote_criteria_type'] = 'contains';
    }

    /** {@inheritdoc} */
    public function getBlockPrefix(): string {
        return 'sylius_customers_autocomplete_choice';
    }

    /** {@inheritdoc} */
    public function getParent(): string {
        return ResourceAutocompleteChoiceType::class;
    }
}