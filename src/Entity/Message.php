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
use Sylius\Component\Customer\Model\CustomerGroup;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @Entity
 * @Table(name="sms77_message")
 */
class Message implements ResourceInterface {
    /** @ORM\ManyToOne(targetEntity="Config", inversedBy="messages") */
    protected $config;

    /**
     * Many Message have Many Configs.
     * @ORM\ManyToMany(targetEntity="Sylius\Component\Customer\Model\CustomerGroup", fetch="EAGER")
     * @ORM\JoinTable(name="sms77_messages_customer_groups",
     *      joinColumns={@ORM\JoinColumn(name="message_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")}
     *      )
     */
    protected $customerGroups;

    /** @var array $customers */
    protected $customers = [];

    /**
     * @Column(type="integer")
     * @Id()
     * @GeneratedValue()
     * @var int $id
     */
    protected $id;

    /**
     * @Column(type="string")
     * @var string $msg
     */
    protected $msg = '';

    /**
     * @Column(type="json", nullable=true)
     * @var string | null $response
     */
    protected $response;

    /**
     * @Column(type="string")
     * @var string $sender
     */
    protected $sender = '';

    public function __construct() {
        $this->customerGroups = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function addCustomerGroup(CustomerGroup $customerGroup): void {
        if (!$this->customerGroups->contains($customerGroup)) {
            $this->customerGroups->add($customerGroup);
        }
    }

    public function removeCustomerGroup(CustomerGroup $customerGroup): bool {
        if ($this->customerGroups->contains($customerGroup)) {
            return $this->customerGroups->removeElement($customerGroup);
        }

        return false;
    }

    public function getCustomerGroups(): Collection {
        return $this->customerGroups;
    }

    public function setCustomerGroups(Collection $customerGroups): void {
        $this->customerGroups = $customerGroups;
    }

    public function getResponse(): ?string {
        return $this->response;
    }

    public function setResponse(?string $response): void {
        $this->response = $response;
    }

    public function getSender(): string {
        return $this->sender;
    }

    public function setSender(string $sender): void {
        $this->sender = $sender;
    }

    public function getMsg(): string {
        return $this->msg;
    }

    public function setMsg(string $msg): void {
        $this->msg = $msg;
    }

    public function getConfig(): ?Config {
        return $this->config;
    }

    public function setConfig(Config $config): void {
        $this->config = $config;
    }

    /** @return array */
    public function getCustomers(): array {
        return $this->customers;
    }

    /** @param array $customers */
    public function setCustomers(array $customers): void {
        $this->customers = $customers;
    }

    /** @param $customer */
    public function addCustomer($customer): void {
        $this->customers[] = $customer;
    }
}