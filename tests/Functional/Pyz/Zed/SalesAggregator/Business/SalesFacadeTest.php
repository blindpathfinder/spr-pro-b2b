<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Functional\Pyz\Zed\SalesAggregator\Business;

use Codeception\TestCase\Test;
use DateTime;
use Orm\Zed\Oms\Persistence\SpyOmsOrderItemState;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Orm\Zed\Sales\Persistence\SpySalesExpense;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Orm\Zed\Sales\Persistence\SpySalesOrderAddress;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemOption;
use Orm\Zed\Shipment\Persistence\SpyShipmentMethodQuery;
use Spryker\Zed\SalesAggregator\Business\SalesAggregatorFacade;

/**
 * @group Pyz
 * @group Zed
 * @group Sales
 * @group SalesFacade
 */
class SalesFacadeTest extends Test
{

    /**
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testSalesOrderAggregatorWithDiscountsStack()
    {
        $salesFacade = $this->createSalesAggregatorFacade();

        $salesOrderEntity = $this->createTestOrder();

        $orderTransfer = $salesFacade->getOrderTotalsByIdSalesOrder($salesOrderEntity->getIdSalesOrder());

        $itemTransfer1 = $orderTransfer->getItems()[0];
        $itemTransfer2 = $orderTransfer->getItems()[1];

        $this->assertSame(500, $itemTransfer1->getUnitGrossPrice());
        $this->assertSame(800, $itemTransfer2->getUnitGrossPrice());

        $this->assertSame(1000, $itemTransfer1->getSumGrossPrice());
        $this->assertSame(800, $itemTransfer2->getSumGrossPrice());

        $this->assertSame(400.0, $itemTransfer1->getUnitGrossPriceWithDiscounts());
        $this->assertSame(700.0, $itemTransfer2->getUnitGrossPriceWithDiscounts());

        $this->assertSame(800.0, $itemTransfer1->getSumGrossPriceWithDiscounts());
        $this->assertSame(700.0, $itemTransfer2->getSumGrossPriceWithDiscounts());

        $this->assertSame(67.06, $itemTransfer1->getUnitTaxAmountWithProductOptionAndDiscountAmounts());
        $this->assertSame(119.75, $itemTransfer2->getUnitTaxAmountWithProductOptionAndDiscountAmounts());

        $this->assertSame(840.0, $itemTransfer1->getSumGrossPriceWithProductOptionAndDiscountAmounts());
        $this->assertSame(750.0, $itemTransfer2->getSumGrossPriceWithProductOptionAndDiscountAmounts());

        $this->assertSame(79.83, $itemTransfer1->getUnitTaxAmount());
        $this->assertSame(127.73, $itemTransfer2->getUnitTaxAmount());

        $this->assertSame(100.0, $itemTransfer1->getUnitTotalDiscountAmount());
        $this->assertSame(100.0, $itemTransfer2->getUnitTotalDiscountAmount());

        $this->assertSame(200.0, $itemTransfer1->getSumTotalDiscountAmount());
        $this->assertSame(100.0, $itemTransfer2->getSumTotalDiscountAmount());

        $this->assertSame(110.0, $itemTransfer1->getUnitTotalDiscountAmountWithProductOption());
        $this->assertSame(110.0, $itemTransfer2->getUnitTotalDiscountAmountWithProductOption());

        $this->assertSame(220.0, $itemTransfer1->getSumTotalDiscountAmountWithProductOption());
        $this->assertSame(110.0, $itemTransfer2->getSumTotalDiscountAmountWithProductOption());

        $this->assertSame(159.67, $itemTransfer1->getSumTaxAmount());
        $this->assertSame(127.73, $itemTransfer2->getSumTaxAmount());

        $this->assertSame(740.0, $itemTransfer1->getRefundableAmount());
        $this->assertSame(650.0, $itemTransfer2->getRefundableAmount());

        $expenseTransfer = $orderTransfer->getExpenses()[0];
        $this->assertSame(90.0, $expenseTransfer->getUnitGrossPriceWithDiscounts());
        $this->assertSame(90.0, $expenseTransfer->getSumGrossPriceWithDiscounts());

        $this->assertSame(14.37, $expenseTransfer->getUnitTaxAmount());
        $this->assertSame(14.37, $expenseTransfer->getSumTaxAmount());

        $this->assertSame(14.37, $expenseTransfer->getUnitTaxAmountWithDiscounts());
        $this->assertSame(14.37, $expenseTransfer->getSumTaxAmountWithDiscounts());

        $this->assertSame(10.0, $expenseTransfer->getUnitTotalDiscountAmount());
        $this->assertSame(10.0, $expenseTransfer->getSumTotalDiscountAmount());

        $this->assertSame(90.0, $expenseTransfer->getRefundableAmount());

        $calculatedDiscountTransfer = $orderTransfer->getCalculatedDiscounts()['discount1'];

        $this->assertSame('discount1', $calculatedDiscountTransfer->getDisplayName());
        $this->assertSame(230.0 + 110.0, $calculatedDiscountTransfer->getSumGrossAmount());

        $totalsTransfer = $orderTransfer->getTotals();
        $this->assertSame(1920, $totalsTransfer->getSubtotal());
        $this->assertSame(100, $totalsTransfer->getExpenseTotal());
        $this->assertSame(340, $totalsTransfer->getDiscountTotal());
        $this->assertSame(1680, $totalsTransfer->getGrandTotal());
        $this->assertSame(268.0, $totalsTransfer->getTaxTotal()->getAmount());
    }

    /**
     * @return void
     */
    public function testSalesOrderAggregatorWithoutDiscounts()
    {
        $salesFacade = $this->createSalesAggregatorFacade();

        $salesOrderEntity = $this->createTestOrder(false);

        $orderTransfer = $salesFacade->getOrderTotalsByIdSalesOrder($salesOrderEntity->getIdSalesOrder());

        $itemTransfer1 = $orderTransfer->getItems()[0];
        $itemTransfer2 = $orderTransfer->getItems()[1];

        $this->assertSame(500, $itemTransfer1->getUnitGrossPrice());
        $this->assertSame(800, $itemTransfer2->getUnitGrossPrice());

        $this->assertSame(1000, $itemTransfer1->getSumGrossPrice());
        $this->assertSame(800, $itemTransfer2->getSumGrossPrice());

        $this->assertSame(500, $itemTransfer1->getUnitGrossPriceWithDiscounts());
        $this->assertSame(800, $itemTransfer2->getUnitGrossPriceWithDiscounts());

        $this->assertSame(1000, $itemTransfer1->getSumGrossPriceWithDiscounts());
        $this->assertSame(800, $itemTransfer2->getSumGrossPriceWithDiscounts());

        $this->assertSame(1060, $itemTransfer1->getSumGrossPriceWithProductOptionAndDiscountAmounts());
        $this->assertSame(860, $itemTransfer2->getSumGrossPriceWithProductOptionAndDiscountAmounts());

        $this->assertSame(79.83, $itemTransfer1->getUnitTaxAmount());
        $this->assertSame(127.73, $itemTransfer2->getUnitTaxAmount());

        $this->assertSame(159.67, $itemTransfer1->getSumTaxAmount());
        $this->assertSame(127.73, $itemTransfer2->getSumTaxAmount());

        $this->assertSame(84.62, $itemTransfer1->getUnitTaxAmountWithProductOptionAndDiscountAmounts());
        $this->assertSame(137.31, $itemTransfer2->getUnitTaxAmountWithProductOptionAndDiscountAmounts());

        $this->assertSame(169.25, $itemTransfer1->getSumTaxAmountWithProductOptionAndDiscountAmounts());
        $this->assertSame(137.31, $itemTransfer2->getSumTaxAmountWithProductOptionAndDiscountAmounts());

        $this->assertSame(1060, $itemTransfer1->getRefundableAmount());
        $this->assertSame(860, $itemTransfer2->getRefundableAmount());

        $expenseTransfer = $orderTransfer->getExpenses()[0];
        $this->assertSame(100, $expenseTransfer->getUnitGrossPriceWithDiscounts());
        $this->assertSame(100, $expenseTransfer->getSumGrossPriceWithDiscounts());

        $this->assertSame(15.97, $expenseTransfer->getUnitTaxAmount());
        $this->assertSame(15.96, $expenseTransfer->getSumTaxAmount());

        $this->assertSame(15.97, $expenseTransfer->getUnitTaxAmountWithDiscounts());
        $this->assertSame(15.96, $expenseTransfer->getSumTaxAmountWithDiscounts());

        $this->assertSame(100, $expenseTransfer->getRefundableAmount());

        $totalsTransfer = $orderTransfer->getTotals();
        $this->assertSame(1920, $totalsTransfer->getSubtotal());
        $this->assertSame(100, $totalsTransfer->getExpenseTotal());
        $this->assertSame(0, $totalsTransfer->getDiscountTotal());
        $this->assertSame(2020, $totalsTransfer->getGrandTotal());
        $this->assertSame(323.0, $totalsTransfer->getTaxTotal()->getAmount());
    }

    /**
     * @return void
     */
    public function testSalesOrderItemWithDiscounts()
    {
        $salesFacade = $this->createSalesAggregatorFacade();

        $salesOrderEntity = $this->createTestOrder();

        $salesOrderItemEntity = $salesOrderEntity->getItems()[0];

        $itemTransfer = $salesFacade->getOrderItemTotalsByIdSalesOrderItem(
            $salesOrderItemEntity->getIdSalesOrderItem()
        );

        $this->assertSame(500, $itemTransfer->getUnitGrossPrice());
        $this->assertSame(1000, $itemTransfer->getSumGrossPrice());

        $this->assertSame(400.0, $itemTransfer->getUnitGrossPriceWithDiscounts());
        $this->assertSame(800.0, $itemTransfer->getSumGrossPriceWithDiscounts());

        $this->assertSame(420.0, $itemTransfer->getUnitGrossPriceWithProductOptionAndDiscountAmounts());
        $this->assertSame(840.0, $itemTransfer->getSumGrossPriceWithProductOptionAndDiscountAmounts());

        $this->assertSame(79.83, $itemTransfer->getUnitTaxAmount());
        $this->assertSame(159.67, $itemTransfer->getSumTaxAmount());

        $this->assertSame(67.06, $itemTransfer->getUnitTaxAmountWithProductOptionAndDiscountAmounts());
        $this->assertSame(134.12, $itemTransfer->getSumTaxAmountWithProductOptionAndDiscountAmounts());

        $this->assertSame(740.0, $itemTransfer->getRefundableAmount());
    }

    /**
     * @return void
     */
    public function testSalesOrderItemWithoutDiscounts()
    {
        $salesFacade = $this->createSalesAggregatorFacade();

        $salesOrderEntity = $this->createTestOrder(false);

        $salesOrderItemEntity = $salesOrderEntity->getItems()[0];

        $itemTransfer = $salesFacade->getOrderItemTotalsByIdSalesOrderItem(
            $salesOrderItemEntity->getIdSalesOrderItem()
        );

        $this->assertSame(500, $itemTransfer->getUnitGrossPrice());
        $this->assertSame(1000, $itemTransfer->getSumGrossPrice());

        $this->assertSame(500, $itemTransfer->getUnitGrossPriceWithDiscounts());
        $this->assertSame(1000, $itemTransfer->getSumGrossPriceWithDiscounts());

        $this->assertSame(530, $itemTransfer->getUnitGrossPriceWithProductOptionAndDiscountAmounts());
        $this->assertSame(1060, $itemTransfer->getSumGrossPriceWithProductOptionAndDiscountAmounts());

        $this->assertSame(79.83, $itemTransfer->getUnitTaxAmount());
        $this->assertSame(159.67, $itemTransfer->getSumTaxAmount());

        $this->assertSame(84.62, $itemTransfer->getUnitTaxAmountWithProductOptionAndDiscountAmounts());
        $this->assertSame(169.25, $itemTransfer->getSumTaxAmountWithProductOptionAndDiscountAmounts());

        $this->assertSame(1060, $itemTransfer->getRefundableAmount());
    }


    /**
     * @param bool $createDiscounts
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    protected function createTestOrder($createDiscounts = true)
    {
        //Data like shipment or state machine is not important in this test so take any first row.
        $salesOrderAddressEntity = new SpySalesOrderAddress();
        $salesOrderAddressEntity->setAddress1(1);
        $salesOrderAddressEntity->setAddress2(2);
        $salesOrderAddressEntity->setSalutation('Mr');
        $salesOrderAddressEntity->setCellPhone('123456789');
        $salesOrderAddressEntity->setCity('City');
        $salesOrderAddressEntity->setCreatedAt(new DateTime());
        $salesOrderAddressEntity->setUpdatedAt(new DateTime());
        $salesOrderAddressEntity->setComment('comment');
        $salesOrderAddressEntity->setDescription('describtion');
        $salesOrderAddressEntity->setCompany('company');
        $salesOrderAddressEntity->setFirstName('First name');
        $salesOrderAddressEntity->setLastName('Last Name');
        $salesOrderAddressEntity->setFkCountry(1);
        $salesOrderAddressEntity->setEmail('email');
        $salesOrderAddressEntity->setZipCode(10405);
        $salesOrderAddressEntity->save();

        $shipmentMethodEntity = SpyShipmentMethodQuery::create()->findOne();

        $omsStateEntity = new SpyOmsOrderItemState();
        $omsStateEntity->setName('test');
        $omsStateEntity->save();

        $salesOrderEntity = new SpySalesOrder();
        $salesOrderEntity->setBillingAddress($salesOrderAddressEntity);
        $salesOrderEntity->setShippingAddress(clone $salesOrderAddressEntity);
        $salesOrderEntity->setShipmentMethod($shipmentMethodEntity);
        $salesOrderEntity->setOrderReference('123');
        $salesOrderEntity->save();

        $this->createOrderItem(
            $omsStateEntity,
            $salesOrderEntity,
            $createDiscounts,
            2,
            500,
            19,
            100,
            'discount1',
            [
                [
                    'gross_price' => 10,
                    'tax_rate' => 19,
                    'discounts' => [
                        [
                            'amount' => 5,
                            'name' => 'discount1'
                        ]
                    ]
                ],
                [
                    'gross_price' => 20,
                    'tax_rate' => 19,
                    'discounts' => [
                        [
                            'amount' => 5,
                            'name' => 'discount1'
                        ]
                    ]
                ]
            ]
        );

        $this->createOrderItem(
            $omsStateEntity,
            $salesOrderEntity,
            $createDiscounts,
            1,
            800,
            19,
            100,
            'discount1',
            [
                [
                    'gross_price' => 20,
                    'tax_rate' => 19,
                    'discounts' => [
                        [
                            'amount' => 5,
                            'name' => 'discount1'
                        ]
                    ]
                ],
                [
                    'gross_price' => 40,
                    'tax_rate' => 19,
                    'discounts' => [
                        [
                            'amount' => 5,
                            'name' => 'discount1'
                        ]
                    ]

                ]
            ]
        );

        $salesExpenseEntity = new SpySalesExpense();
        $salesExpenseEntity->setName('shipping test');
        $salesExpenseEntity->setTaxRate(19);
        $salesExpenseEntity->setGrossPrice(100);
        $salesExpenseEntity->setFkSalesOrder($salesOrderEntity->getIdSalesOrder());
        $salesExpenseEntity->save();

        if ($createDiscounts === true) {
            $this->createSalesDiscount(
                10,
                'discount1',
                $salesOrderEntity->getIdSalesOrder(),
                null,
                $salesExpenseEntity->getIdSalesExpense()
            );
        }

        return $salesOrderEntity;
    }


    /**
     * @return \Spryker\Zed\SalesAggregator\Business\SalesAggregatorFacade
     */
    protected function createSalesAggregatorFacade()
    {
        return new SalesAggregatorFacade();
    }

    /**
     * @param \Orm\Zed\Oms\Persistence\SpyOmsOrderItemState $omsState
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $salesOrder
     * @param int $quantity
     * @param bool $createDiscounts
     * @param int $grossPrice
     * @param int $taxRate
     * @param int $discountAmount
     * @param int $discountName
     * @param array $options
     *
     * @throws \Propel\Runtime\Exception\PropelException
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrderItem
     */
    protected function createOrderItem(
        SpyOmsOrderItemState $omsState,
        SpySalesOrder $salesOrder,
        $createDiscounts,
        $quantity,
        $grossPrice,
        $taxRate,
        $discountAmount,
        $discountName,
        array $options = []
    ) {
        $salesOrderItem = new SpySalesOrderItem();
        $salesOrderItem->setGrossPrice($grossPrice);
        $salesOrderItem->setQuantity($quantity);
        $salesOrderItem->setSku('123');
        $salesOrderItem->setName('test1');
        $salesOrderItem->setTaxRate($taxRate);
        $salesOrderItem->setFkOmsOrderItemState($omsState->getIdOmsOrderItemState());
        $salesOrderItem->setFkSalesOrder($salesOrder->getIdSalesOrder());
        $salesOrderItem->save();

        if ($createDiscounts === true) {
            $this->createSalesDiscount(
                $discountAmount,
                $discountName,
                $salesOrder->getIdSalesOrder(),
                $salesOrderItem->getIdSalesOrderItem()
            );
        }

        foreach ($options as $option) {
            $salesOrderItemOption = new SpySalesOrderItemOption();
            $salesOrderItemOption->setFkSalesOrderItem($salesOrderItem->getIdSalesOrderItem());
            $salesOrderItemOption->setGrossPrice($option['gross_price']);
            $salesOrderItemOption->setTaxRate($option['tax_rate']);
            $salesOrderItemOption->setGroupName('label1');
            $salesOrderItemOption->setValue('value1');
            $salesOrderItemOption->save();
            if (isset($option['discounts'])) {
                foreach ($option['discounts'] as $discount) {
                    if ($createDiscounts === true) {
                        $this->createSalesDiscount(
                            $discount['amount'],
                            $discount['name'],
                            $salesOrder->getIdSalesOrder(),
                            $salesOrderItem->getIdSalesOrderItem(),
                            null,
                            $salesOrderItemOption->getIdSalesOrderItemOption()
                        );
                    }
                }
            }
        }

        return $salesOrderItem;
    }

    /**
     * @param int $amount
     * @param int $name
     * @param int $idOrder
     * @param int $idOrderItem
     * @param int $idExpense
     * @param int $idOrderItemOption
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    protected function createSalesDiscount(
        $amount,
        $name,
        $idOrder,
        $idOrderItem = null,
        $idExpense = null,
        $idOrderItemOption = null
    ) {
        $spySalesDiscount = new SpySalesDiscount();
        $spySalesDiscount->setName('name');
        $spySalesDiscount->setFkSalesOrder($idOrder);
        $spySalesDiscount->setFkSalesOrderItem($idOrderItem);
        $spySalesDiscount->setFkSalesExpense($idExpense);
        $spySalesDiscount->setFkSalesOrderItemOption($idOrderItemOption);
        $spySalesDiscount->setDisplayName($name);
        $spySalesDiscount->setAmount($amount);
        $spySalesDiscount->save();
    }

}
