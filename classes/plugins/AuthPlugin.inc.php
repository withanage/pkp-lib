<?php

/**
 * @file classes/plugins/AuthPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for authentication plugins.
 *
 * TODO: Error reporting when updating remote source fails.
 * TODO: Support importing user accounts from the authentication source into OJS/OMP.
 */

namespace PKP\plugins;

define('AUTH_PLUGIN_CATEGORY', 'auth');
import('lib.pkp.classes.plugins.Plugin');

abstract class AuthPlugin extends Plugin
{
    /** @var array $settings settings for this plugin instance */
    public $settings;

    /** @var int $authId auth source ID for this plugin instance */
    public $authId;

    /**
     * Constructor.
     *
     * @param $settings array
     * @param $authId int ID for this instance
     */
    public function __construct($settings = [], $authId = null)
    {
        parent::__construct();
        $this->settings = $settings;
        $this->authId = $authId;
    }


    //
    // General Plugin Functions
    //
    /**
     * Return the path to a template for plugin settings.
     * Can return null if there are no plugin-specific settings.
     *
     * @return string
     */
    public function getSettingsTemplate()
    {
        return $this->getTemplateResource('settings.tpl');
    }


    //
    // Wrapper Functions
    //
    /**
     * Update local user profile from the remote source, if enabled.
     *
     * @param $user User
     *
     * @return boolean true if successful
     */
    public function doGetUserInfo($user)
    {
        if (isset($this->settings['syncProfiles'])) {
            return $this->getUserInfo($user);
        }
        return false;
    }

    /**
     * Update remote user profile, if enabled.
     *
     * @param $user User
     *
     * @return boolean true if successful
     */
    public function doSetUserInfo($user)
    {
        if (isset($this->settings['syncProfiles'])) {
            return $this->setUserInfo($user);
        }
        return false;
    }

    /**
     * Update remote user password, if enabled.
     *
     * @param $username string
     * @param $password string
     *
     * @return boolean true if successful
     */
    public function doSetUserPassword($username, $password)
    {
        if (isset($this->settings['syncPasswords'])) {
            return $this->setUserPassword($username, $password);
        }
        return false;
    }

    /**
     * Create remote user account, if enabled.
     *
     * @param $user User to create
     *
     * @return boolean true if successful
     */
    public function doCreateUser($user)
    {
        if (isset($this->settings['createUsers'])) {
            return $this->createUser($user);
        }
        return false;
    }


    //
    // Core Plugin Functions
    // (Must be implemented by every authentication plugin)
    //
    /**
     * Returns an instance of the authentication plugin
     *
     * @param $settings array settings specific to this instance
     * @param $authId int identifier for this instance
     *
     * @return AuthPlugin
     */
    abstract public function getInstance($settings, $authId);

    /**
     * Authenticate a username and password.
     *
     * @param $username string
     * @param $password string
     *
     * @return boolean true if authentication is successful
     */
    abstract public function authenticate($username, $password);


    //
    // Optional Plugin Functions
    // (Required for extended functionality but not for authentication-only plugins)
    //
    /**
     * Check if a username exists.
     *
     * @param $username string
     *
     * @return boolean
     */
    public function userExists($username)
    {
        return false;
    }

    /**
     * Retrieve user profile information from the remote source.
     * Any unsupported fields (e.g., OJS- or OMP-specific ones) should not be modified.
     *
     * @param $user User to update
     *
     * @return boolean true if successful
     */
    public function getUserInfo($user)
    {
        return false;
    }

    /**
     * Store user profile information on the remote source.
     *
     * @param $user User to store
     *
     * @return boolean true if successful
     */
    public function setUserInfo($user)
    {
        return false;
    }

    /**
     * Change a user's password on the remote source.
     *
     * @param $username string user to update
     * @param $password string the new password
     *
     * @return boolean true if successful
     */
    public function setUserPassword($username, $password)
    {
        return false;
    }

    /**
     * Create a user on the remote source.
     *
     * @param $user User to create
     *
     * @return boolean true if successful
     */
    public function createUser($user)
    {
        return false;
    }

    /**
     * Delete a user from the remote source.
     * This function is currently not used within OJS or OMP,
     * but is reserved for future use.
     *
     * @param $username string user to delete
     *
     * @return boolean true if successful
     */
    public function deleteUser($username)
    {
        return false;
    }

    /**
     * Return true iff this is a site-wide plugin.
     */
    public function isSitePlugin()
    {
        return true;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\PKP\plugins\AuthPlugin', '\AuthPlugin');
}
