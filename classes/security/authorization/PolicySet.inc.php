<?php
/**
 * @file classes/security/authorization/PolicySet.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PolicySet
 * @ingroup security_authorization
 *
 * @brief An ordered list of policies. Policy sets can be added to
 *  decision managers like policies. The decision manager will evaluate
 *  the contained policies in the order they were added.
 *
 *  NB: PolicySets can be nested.
 */

define('COMBINING_DENY_OVERRIDES', 0x01);
define('COMBINING_PERMIT_OVERRIDES', 0x02);

// Include the authorization policy class which contains
// definitions for the deny and permit effects.
import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class PolicySet
{
    /** @var array */
    public $_policies = [];

    /** @var integer */
    public $_combiningAlgorithm;

    /** @var integer the default effect if none of the policies in the set applies */
    public $_effectIfNoPolicyApplies = AUTHORIZATION_DENY;


    /**
     * Constructor
     *
     * @param $combiningAlgorithm int COMBINING_...
     */
    public function __construct($combiningAlgorithm = COMBINING_DENY_OVERRIDES)
    {
        $this->_combiningAlgorithm = $combiningAlgorithm;
    }

    //
    // Setters and Getters
    //
    /**
     * Add a policy or a nested policy set.
     *
     * @param $policyOrPolicySet AuthorizationPolicy|PolicySet
     * @param $addToTop boolean whether to insert the new policy
     *  to the top of the list.
     */
    public function addPolicy($policyOrPolicySet, $addToTop = false)
    {
        assert(is_a($policyOrPolicySet, 'AuthorizationPolicy') || is_a($policyOrPolicySet, 'PolicySet'));
        if ($addToTop) {
            array_unshift($this->_policies, $policyOrPolicySet);
        } else {
            $this->_policies[] = & $policyOrPolicySet;
        }
    }

    /**
     * Get all policies within this policy set.
     *
     * @return array a list of AuthorizationPolicy or PolicySet objects.
     */
    public function &getPolicies()
    {
        return $this->_policies;
    }

    /**
     * Return the combining algorithm
     *
     * @return integer
     */
    public function getCombiningAlgorithm()
    {
        return $this->_combiningAlgorithm;
    }

    /**
     * Set the default effect if none of the policies in the set applies
     *
     * @param $effectIfNoPolicyApplies integer
     */
    public function setEffectIfNoPolicyApplies($effectIfNoPolicyApplies)
    {
        assert($effectIfNoPolicyApplies == AUTHORIZATION_PERMIT ||
                $effectIfNoPolicyApplies == AUTHORIZATION_DENY ||
                $effectIfNoPolicyApplies == AUTHORIZATION_NOT_APPLICABLE);
        $this->_effectIfNoPolicyApplies = $effectIfNoPolicyApplies;
    }

    /**
     * Get the default effect if none of the policies in the set applies
     *
     * @return integer
     */
    public function getEffectIfNoPolicyApplies()
    {
        return $this->_effectIfNoPolicyApplies;
    }
}
