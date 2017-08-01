<?php
declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\CartBundle\Domain\Delivery;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRuleBuilder;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethod;

class DeliveryCalculator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    public function __construct(
        Connection $connection,
        PriceCalculator $priceCalculator,
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder
    ) {
        $this->connection = $connection;
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
    }

    public function calculate(Delivery $delivery, ShopContextInterface $context): void
    {
        switch ($delivery->getShippingMethod()->getCalculation()) {
            case ShippingMethod::CALCULATION_BY_WEIGHT:
                $costs = $this->calculateShippingCosts(
                    $this->findShippingCosts(
                        $delivery->getShippingMethod(),
                        $delivery->getPositions()->getWeight()
                    ),
                    $delivery->getPositions()->getLineItems(),
                    $context
                );

                break;
            case ShippingMethod::CALCULATION_BY_PRICE:
                $costs = $this->calculateShippingCosts(
                    $this->findShippingCosts(
                        $delivery->getShippingMethod(),
                        $delivery->getPositions()->getPrices()->sum()->getTotalPrice()
                    ),
                    $delivery->getPositions()->getLineItems(),
                    $context
                );

                break;

            case ShippingMethod::CALCULATION_BY_LINE_ITEM_COUNT:
                $costs = $this->calculateShippingCosts(
                    $this->findShippingCosts(
                        $delivery->getShippingMethod(),
                        $delivery->getPositions()->getQuantity()
                    ),
                    $delivery->getPositions()->getLineItems(),
                    $context
                );
                break;

            case ShippingMethod::CALCULATION_BY_CUSTOM:

                return;
        }

        $delivery->setShippingCosts($costs);
    }

    private function calculateShippingCosts(float $price, CalculatedLineItemCollection $calculatedLineItems, ShopContextInterface $context): Price
    {
        $rules = $this->percentageTaxRuleBuilder->buildRules(
            $calculatedLineItems->getPrices()->sum()
        );

        $definition = new PriceDefinition($price, $rules, 1, true);

        return $this->priceCalculator->calculate($definition, $context);
    }

    private function findShippingCosts(ShippingMethod $shippingMethod, float $value): float
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('costs.value');
        $query->from('s_premium_shippingcosts', 'costs');
        $query->andWhere('costs.`from` <= :value');
        $query->andWhere('costs.dispatchID = :id');
        $query->setParameter(':id', $shippingMethod->getId());
        $query->setParameter(':value', $value);
        $query->addOrderBy('value', 'DESC');
        $query->setMaxResults(1);

        return (float) $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }
}