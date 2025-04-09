<?php 
namespace Fermion\LoyaltyPoint\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class GenerateCouponCodeAndLoyaltyPoints implements ObserverInterface
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();

        // Calculate coupon code and loyalty points (customize this logic)
        $couponCode = $this->generateCouponCode();
        $loyaltyPoints = $this->calculateLoyaltyPoints();

        try {
            // Save the coupon code and loyalty points to the customer attributes
            $customer->setData('coupon_code', $couponCode);
            $customer->setData('loyalty_point', $loyaltyPoints);
            $this->customerRepository->save($customer);
        } catch (LocalizedException $e) {
            echo "Exception";
            // Handle the exception if there's an issue saving the attributes
        }
    }

    private function generateCouponCode()
    {
        // Generate your coupon code logic here (e.g., using random characters)
        // Make sure it's unique for each customer.

        return 'YOUR_GENERATED_COUPON_CODE';
    }

    private function calculateLoyaltyPoints()
    {
        // Calculate your loyalty points based on custom logic (e.g., purchases, actions, etc.).

        return 100; // Replace with the actual calculation result.
    }
}