<?php declare(strict_types=1);

namespace Sms77\SyliusPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

/**
 * @Entity
 * @Table(name="sms77_config")
 */
class Config implements ResourceInterface, TranslatableInterface {
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    /**
     * @Column(type="string")
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @Column(type="string", nullable=true)
     * @var string $label
     */
    protected $label;

    /**
     * @Column(type="integer")
     * @Id()
     * @GeneratedValue()
     * @var integer $id
     */
    protected $id;

    /**
     * @Column(type="boolean")
     * @var bool $onShipping
     */
    protected $onShipping = false;

    /**
     * @Column(type="boolean")
     * @var bool $enabled
     */
    protected $enabled = true;

    /**
     * @Column(type="boolean")
     * @var bool $debug
     */
    protected $debug = false;

    /**
     * @var Message[] $messages
     * @ORM\OneToMany(targetEntity="Message", mappedBy="config")
     */
    private $messages;

    public function __construct() {
        $this->initializeTranslationsCollection();

        $this->messages = new ArrayCollection();
    }

    public function __toString(): string {
        return $this->label ?? (string)$this->id;
    }

    public function getApiKey(): ?string {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void {
        $this->apiKey = $apiKey;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    public function setLabel(?string $label): void {
        $this->label = $label;
    }

    public function setSender(?string $sender): void {
        $this->getTranslation()->setSender($sender);
    }

    public function getSender(): ?string {
        return $this->getTranslation()->getSender();
    }

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection {
        return $this->messages;
    }

    /**
     * @return Collection|Message[]
     */
    public function setMessages(): Collection {
        return $this->messages;
    }

    public function getOnShipping(): bool {
        return $this->onShipping;
    }

    public function setOnShipping(bool $onShipping): void {
        $this->onShipping = $onShipping;
    }

    public function getDebug(): bool {
        return $this->debug;
    }

    public function setDebug(bool $debug): void {
        $this->debug = $debug;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function setShippingText(?string $shippingText): void {
        $this->getTranslation()->setShippingText($shippingText);
    }

    public function getShippingText(): ?string {
        return $this->getTranslation()->getShippingText();
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslation(): ConfigTranslation {
        return new ConfigTranslation();
    }
}