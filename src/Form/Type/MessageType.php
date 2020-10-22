<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Form\Type;

use Sms77\SyliusPlugin\Entity\Message;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;

class MessageType extends AbstractResourceType {
    /** @var CustomerRepositoryInterface $customerRepository */
    private $customerRepository;

    public function __construct(string $dataClass, array $validationGroups = [], CustomerRepositoryInterface $customerRepository) {
        parent::__construct($dataClass, $validationGroups);

        $this->customerRepository = $customerRepository;
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /* @var Message $message */
        $message = $builder->getData();

        if (null !== $message->getId()) {
            $builder->add(
                'response', TextareaType::class, ['attr' => ['readonly' => true],]);
        }

        $builder
            ->add('config', ConfigType::class, ['label' => false])
            ->add('customers', CustomersAutocompleteChoiceType::class, [
                'choice_name' => 'lastName',
                'choice_value' => 'id',
                'label' => 'sylius.ui.customer',
                'multiple' => true,
                'resource' => 'sylius.customer',
            ])
            ->add('customerGroups', EntityType::class, [
                'class' => CustomerGroup::class,
                'multiple' => true,
            ])
            ->add('sender',
                TextType::class, ['data' => $message->getConfig()->getSender(),])
            ->add('msg', TextareaType::class);

        $builder->get('config')
            ->remove('apiKey')
            ->remove('enabled')
            ->remove('label')
            ->remove('onShipping')
            ->remove('translations');

        $builder->get('customers')
            ->addModelTransformer(
                new ReversedTransformer($this->customerIdentifierTransformer()))
            ->addModelTransformer($this->customerIdentifierTransformer());
    }

    private function customerIdentifierTransformer(): ResourceToIdentifierTransformer {
        return new ResourceToIdentifierTransformer($this->customerRepository, 'id');
    }

    /** {@inheritdoc} */
    public function getBlockPrefix() {
        return 'sms77_message';
    }
}