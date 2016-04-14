<?php

/**
 * Custom Admin Login form screen
 * 
 * This login screen get also ip based access protection when enabled
 *
 * @property Security|AdminLoginExtension $owner
 */
class AdminLoginExtension extends Extension
{

    /**
     * Redirect to AdminSecurity, when we are coming from /admin/*
     *
     * @return SS_HTTPResponse|void
     */
    public function onBeforeSecurityLogin()
    {
        $backUrl = $this->owner->getRequest()->getVar('BackURL');
        if (strstr($backUrl, '/admin/')) {
            if (Controller::curr()->class != 'AdminSecurity') {
                $link = 'AdminSecurity/login' . '?BackURL=' . urlencode($backUrl);
                return $this->owner->redirect($link);
            }
        }
    }
}
