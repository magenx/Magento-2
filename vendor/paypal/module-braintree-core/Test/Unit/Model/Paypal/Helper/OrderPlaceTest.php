<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PayPal\Braintree\Test\Unit\Model\Paypal\Helper;

use PayPal\Braintree\Model\Paypal\Helper\OrderPlace;
use PayPal\Braintree\Model\Paypal\OrderCancellationService;
use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;

/**
 * @see \PayPal\Braintree\Model\Paypal\Helper\OrderPlace
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderPlaceTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_EMAIL = 'test@test.loc';

    /**
     * @var CartManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartManagementMock;

    /**
     * @var AgreementsValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $agreementsValidatorMock;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var Address|\PHPUnit\Framework\MockObject\MockObject
     */
    private $billingAddressMock;

    /**
     * @var OrderPlace
     */
    private $orderPlace;

    /**
     * @var OrderCancellationService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderCancellationServiceMock;

    protected function setUp(): void
    {
        $this->cartManagementMock = $this->getMockBuilder(CartManagementInterface::class)
            ->getMockForAbstractClass();
        $this->agreementsValidatorMock = $this->getMockBuilder(AgreementsValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderCancellationServiceMock = $this->getMockBuilder(OrderCancellationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderPlace = new OrderPlace(
            $this->cartManagementMock,
            $this->agreementsValidatorMock,
            $this->customerSessionMock,
            $this->checkoutHelperMock,
            $this->orderCancellationServiceMock
        );
    }

    public function testExecuteGuest()
    {
        $this->markTestSkipped('Skip this test');
        $agreement = ['test', 'test'];
        $quoteMock = $this->getQuoteMock();

        $this->agreementsValidatorMock->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $this->getCheckoutMethodStep($quoteMock);
        $this->prepareGuestQuoteStep($quoteMock);
        $this->disabledQuoteAddressValidationStep($quoteMock);

        $quoteMock->expects(self::once())
            ->method('collectTotals');

        $quoteMock->expects(self::once())
            ->method('getId')
            ->willReturn(10);

        $this->cartManagementMock->expects(self::once())
            ->method('placeOrder')
            ->with(10);

        $this->orderPlace->execute($quoteMock, $agreement);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $quoteMock
     */
    private function disabledQuoteAddressValidationStep(\PHPUnit\Framework\MockObject\MockObject $quoteMock)
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);
        $shippingAddressMock = $this->getMockBuilder(Address::class)
            ->setMethods(['setShouldIgnoreValidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->expects(self::once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddressMock);

        $billingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $quoteMock->expects(self::once())
            ->method('getIsVirtual')
            ->willReturn(false);

        $shippingAddressMock->expects(self::once())
            ->method('setShouldIgnoreValidation')
            ->with(true)
            ->willReturnSelf();

        $billingAddressMock->expects(self::any())
            ->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $billingAddressMock->expects(self::never())
            ->method('setSameAsBilling');
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $quoteMock
     */
    private function getCheckoutMethodStep(\PHPUnit\Framework\MockObject\MockObject $quoteMock)
    {
        $this->customerSessionMock->expects(self::once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $quoteMock->expects(self::at(1))
            ->method('getCheckoutMethod')
            ->willReturn(null);

        $this->checkoutHelperMock->expects(self::once())
            ->method('isAllowedGuestCheckout')
            ->with($quoteMock)
            ->willReturn(true);

        $quoteMock->expects(self::once())
            ->method('setCheckoutMethod')
            ->with(Onepage::METHOD_GUEST);

        $quoteMock->expects(self::at(2))
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $quoteMock
     */
    private function prepareGuestQuoteStep(\PHPUnit\Framework\MockObject\MockObject $quoteMock)
    {
        $billingAddressMock = $this->getBillingAddressMock($quoteMock);

        $quoteMock->expects(self::once())
            ->method('setCustomerId')
            ->with(null)
            ->willReturnSelf();

        $billingAddressMock->expects(self::at(0))
            ->method('getEmail')
            ->willReturn(self::TEST_EMAIL);

        $quoteMock->expects(self::once())
            ->method('setCustomerEmail')
            ->with(self::TEST_EMAIL)
            ->willReturnSelf();

        $quoteMock->expects(self::once())
            ->method('setCustomerIsGuest')
            ->with(true)
            ->willReturnSelf();

        $quoteMock->expects(self::once())
            ->method('setCustomerGroupId')
            ->with(Group::NOT_LOGGED_IN_ID)
            ->willReturnSelf();
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $quoteMock
     * @return Address|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getBillingAddressMock(\PHPUnit\Framework\MockObject\MockObject $quoteMock)
    {
        if (!isset($this->billingAddressMock)) {
            $this->billingAddressMock = $this->getMockBuilder(Address::class)
                ->setMethods(['setShouldIgnoreValidation', 'getEmail', 'setSameAsBilling'])
                ->disableOriginalConstructor()
                ->getMock();
        }

        $quoteMock->expects(self::any())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddressMock);

        return $this->billingAddressMock;
    }

    /**
     * @return Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->setMethods(
                [
                    'setCustomerId',
                    'setCustomerEmail',
                    'setCustomerIsGuest',
                    'setCustomerGroupId',
                    'getCheckoutMethod',
                    'setCheckoutMethod',
                    'collectTotals',
                    'getId',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getIsVirtual'
                ]
            )->disableOriginalConstructor()
            ->getMock();
    }
}
