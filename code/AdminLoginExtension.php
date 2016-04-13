<?php

/**
 * Custom Admin Login form screen
 * This login screen get also ip based access protection when enabled
 */
class AdminLoginExtension extends Extension
{

    // redirect to AdminSecurity, when we are coming from /admin/*
    public function onBeforeSecurityLogin()
    {
        if (isset($_GET['BackURL']) && strstr($_GET['BackURL'], '/admin/')) {
            if (Controller::curr()->class != 'AdminSecurity') {
                $link = 'AdminSecurity/login' . '?BackURL=' . urlencode($_GET['BackURL']);
                return $this->owner->redirect($link);
            }
        }
    }
}
