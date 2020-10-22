<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Controller;

use FOS\RestBundle\View\View;
use Sms77\Api\Client;
use Sms77\SyliusPlugin\Entity\Message;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Customer\Model\CustomerGroup;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageController extends ResourceController {
    public function createAction(Request $request): Response {
        $requestConfig =
            $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($requestConfig, ResourceActions::CREATE);
        /* @var Message $newResource */
        $newResource = $this->newResourceFactory->create($requestConfig, $this->factory);

        $cfgId = $request->get('config');
        $cfgRepo = $this->get('sms77.repository.config');
        $newResource->setConfig($cfgId ? $cfgRepo->find($cfgId) : $cfgRepo->findEnabled());

        //dd($newResource);

        $form = $this->resourceFormFactory->create($requestConfig, $newResource);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->handlePOST($form, $requestConfig);
        }

        if (!$requestConfig->isHtmlRequest()) {
            return $this->viewHandler->handle(
                $requestConfig, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEventResponse =
            $this->eventDispatcher
                ->dispatchInitializeEvent(
                    ResourceActions::CREATE, $requestConfig, $newResource)
                ->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        return $this->viewHandler->handle(
            $requestConfig,
            View::create()
                ->setData([
                    'configuration' => $requestConfig,
                    'metadata' => $this->metadata,
                    'resource' => $newResource,
                    $this->metadata->getName() => $newResource,
                    'form' => $form->createView(),
                ])
                ->setTemplate(
                    $requestConfig->getTemplate(ResourceActions::CREATE . '.html'))
        );
    }

    private function handlePOST(
        FormInterface $form, RequestConfiguration $requestConfig): Response {
        $newResource = $form->getData();

        $newResource->setResponse($this->getResponse($newResource));

        $event = $this->eventDispatcher->dispatchPreEvent(
            ResourceActions::CREATE, $requestConfig, $newResource);

        if ($event->isStopped() && !$requestConfig->isHtmlRequest()) {
            throw new HttpException($event->getErrorCode(), $event->getMessage());
        }
        if ($event->isStopped()) {
            $this->flashHelper->addFlashFromEvent($requestConfig, $event);

            $eventResponse = $event->getResponse();
            if (null !== $eventResponse) {
                return $eventResponse;
            }

            return $this->redirectHandler->redirectToIndex($requestConfig, $newResource);
        }

        if ($requestConfig->hasStateMachine()) {
            $this->stateMachine->apply($requestConfig, $newResource);
        }

        $this->repository->add($newResource);

        if ($requestConfig->isHtmlRequest()) {
            $this->flashHelper->addSuccessFlash(
                $requestConfig, ResourceActions::CREATE, $newResource);
        }

        $postEvent = $this->eventDispatcher->dispatchPostEvent(
            ResourceActions::CREATE, $requestConfig, $newResource);

        if (!$requestConfig->isHtmlRequest()) {
            return $this->viewHandler->handle(
                $requestConfig, View::create($newResource, Response::HTTP_CREATED));
        }

        $postEventResponse = $postEvent->getResponse();
        if (null !== $postEventResponse) {
            return $postEventResponse;
        }

        return $this->redirectHandler->redirectToResource($requestConfig, $newResource);
    }

    private function getResponse(Message $newResource): array {
        $customerGroups = [];
        foreach ($newResource->getCustomerGroups()->toArray() as $customerGroup) {
            /** @var CustomerGroup $customerGroup */
            $customerGroups[] = $customerGroup->getId();
        }

        /** @var CustomerRepository $customerRepo */
        $customerRepo = $this->manager->getRepository(Customer::class);
        /* @var CustomerInterface[] $customers */
        $customers = 0 !== count($customerGroups)
            ? $customerRepo->findBy(['group' => $customerGroups])
            : $customerRepo->findAll();

        $text = $newResource->getMsg();
        $apiRequests = [];
        $isPersonalized = false !== strpos($newResource->getMsg(), '{0}');
        foreach ($customers as $customer) {
            $phone = $customer->getPhoneNumber() ?? '';

            if ('' === $phone) {
                continue;
            }

            $apiRequests[$phone] = $isPersonalized
                ? str_replace('{0}', $customer->getFullName(), $text)
                : $text;
        }

        $client = new Client($newResource->getConfig()->getApiKey(), 'sylius');
        foreach ($apiRequests as $to => $text) {
            $responses[] = $client->sms($to, $text, ['json' => 1]);
        }

        return $responses ?? [];
    }

    public function indexAction(Request $request): Response {
        $configuration =
            $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);
        $resources =
            $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatchMultiple(
            ResourceActions::INDEX, $configuration, $resources);

        $view = View::create($resources);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::INDEX . '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resources' => $resources,
                    'message_configurations' =>
                        $this->get('sms77.repository.config')->findAll(),
                    $this->metadata->getPluralName() => $resources,
                ]);
        }

        return $this->viewHandler->handle($configuration, $view);
    }
}