<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Api;

use Sms77\Api\Client as ApiClient;
use Sms77\SyliusPlugin\Repository\ConfigRepository;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Core\Model\ShipmentInterface;

class Client {
    /* @var Configuration $configuration */
    protected $configuration;

    public function __construct(ConfigRepository $configurationRepo) {
        $this->configuration = $configurationRepo->findEnabled();
    }

    public function sms(ShipmentInterface $shipment): void {
        $state = null;
        $to = null;
        $text = null;

        $state = $shipment->getState();
        $to = $this->orderToPhone($shipment->getOrder());
        $text = $this->stateToText($state);

        $client = $this->initApi();

        if (null !== $to && null !== $client) {
            $extras = [];

            $sender = $this->configuration->getSender();
            if (null !== $sender) {
                $extras['from'] = $sender;
            }

            $client->sms($to, $text, $extras);
        }
    }

    private function orderToPhone(OrderInterface $order): ?string {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        return null === $shippingAddress->getPhoneNumber()
            ? $billingAddress->getPhoneNumber()
            : $shippingAddress->getPhoneNumber();
    }

    private function stateToText(string $state): ?string {
        if (null !== $this->configuration
            && OrderShippingStates::STATE_SHIPPED === $state) {
            return $this->configuration->getShippingText();
        }

        return null;
    }

    private function initApi(): ?ApiClient {
        $client = null;

        if (null !== $this->configuration) {
            $apiKey = $this->configuration->getApiKey();

            if (isset($apiKey)) {
                $client = new ApiClient($this->configuration->getApiKey(), 'sylius');
            }
        }

        return $client;
    }
}