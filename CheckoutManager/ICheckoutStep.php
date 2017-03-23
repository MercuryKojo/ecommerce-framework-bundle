<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @category   Pimcore
 * @package    EcommerceFramework
 * @copyright  Copyright (c) 2009-2016 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


namespace Pimcore\Bundle\PimcoreEcommerceFrameworkBundle\CheckoutManager;

/**
 * Interface for checkout step implementations of online shop framework
 */
interface ICheckoutStep {

    /**
     * @param \Pimcore\Bundle\PimcoreEcommerceFrameworkBundle\CartManager\ICart $cart
     */
    public function __construct(\Pimcore\Bundle\PimcoreEcommerceFrameworkBundle\CartManager\ICart $cart);

    /**
     * @return string
     */
    public function getName();

    /**
     * returns saved data of step
     *
     * @return mixed
     */
    public function getData();

    /**
     * sets delivered data and commits step
     *
     * @param  $data
     * @return bool
     */
    public function commit($data);

}
