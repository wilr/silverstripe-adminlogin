<?php

class LimitAdminAccessExtension extends Extension
{
    
    public function onBeforeInit()
    {
        if (Config::inst()->get('IpAccess', 'enabled')) {
            $ipAccess = new IpAccess($this->owner->getRequest()->getIP(), Config::inst()->get('IpAccess', 'allowed_ips'));
                
            if (!$ipAccess->hasAccess()) {
                if (class_exists('ErrorPage', true)) {
                    $response = ErrorPage::response_for(403);
                }

                $response = ($response) ? $response : 'The requested page could not be found.';

                return $this->owner->httpError(403, $response);
            }
        }
    }
}
