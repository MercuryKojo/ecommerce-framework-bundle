<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\EcommerceFrameworkBundle\PricingManager\Action;

use Pimcore\Bundle\EcommerceFrameworkBundle\CartManager\CartPriceModificator\Discount;
use Pimcore\Bundle\EcommerceFrameworkBundle\PricingManager\ActionInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\PricingManager\EnvironmentInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\Type\Decimal;

// TODO use Decimal for amounts?
class CartDiscount implements DiscountInterface, CartActionInterface
{
    protected float $amount = 0;

    protected float $percent = 0;

    protected bool $onlyDiscountCart = false;

    public function executeOnCart(EnvironmentInterface $environment): ActionInterface
    {
        $priceCalculator = $environment->getCart()->getPriceCalculator();

        $subTotal = $priceCalculator->getSubTotal()->getAmount();

        $amount = Decimal::create($this->amount);

        if ($this->onlyDiscountCart && $subTotal->sub($amount)->isNegative()) {
            // prevent discounted amount to be higher than the subtotal
            $amount = $subTotal;
        } elseif ($amount->isZero()) {
            $amount = $subTotal->toPercentage($this->getPercent());
            // round to 2 digits for further calculations to avoid rounding issues at later point
            $amount = Decimal::fromDecimal($amount->withScale(2));
        }

        $amount = $amount->toAdditiveInverse();

        //make sure that one rule is applied only once
        foreach ($priceCalculator->getModificators() as &$modificator) {
            if ($modificator instanceof Discount && $modificator->getRuleId() == $environment->getRule()->getId()) {
                $modificator->setAmount($amount);
                $priceCalculator->calculate(true);

                return $this;
            }
        }

        $modDiscount = new Discount($environment->getRule());
        $modDiscount->setAmount($amount);

        $priceCalculator->addModificator($modDiscount);
        $priceCalculator->calculate(true);

        return $this;
    }

    public function toJSON(): string
    {
        return json_encode([
            'type' => 'CartDiscount',
            'amount' => $this->getAmount(),
            'percent' => $this->getPercent(),
            'onlyDiscountCart' => $this->onlyDiscountCart(),
        ]);
    }

    public function fromJSON(string $string): ActionInterface
    {
        $json = json_decode($string);
        if ($json->amount) {
            if ($json->amount < 0) {
                throw new \Exception('Only positive numbers and 0 are valid values for absolute discounts');
            }

            $this->setAmount($json->amount);
        }
        if ($json->percent) {
            if ($json->percent < 0) {
                throw new \Exception('Only positive numbers and 0 are valid values for % discounts');
            }

            $this->setPercent($json->percent);
        }

        $this->setOnlyDiscountCart($json->onlyDiscountCart ?? false);

        return $this;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setPercent(float $percent): void
    {
        $this->percent = $percent;
    }

    public function getPercent(): float
    {
        return $this->percent;
    }

    public function setOnlyDiscountCart(bool $onlyDiscountCart): void
    {
        $this->onlyDiscountCart = $onlyDiscountCart;
    }

    public function onlyDiscountCart(): bool
    {
        return $this->onlyDiscountCart;
    }
}
