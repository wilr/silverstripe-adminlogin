<?php

/**
 * Class LimitAdminAccessExtension
 *
 * @property LeftAndMain|LimitAdminAccessExtension $owner
 */
class LimitAdminAccessExtension extends Extension
{
    /**
     * @return mixed
     */
    public function onBeforeInit()
    {
        $access = new IpAccess($this->owner->getRequest()->getIP());
        if (!$access->hasAccess()) {
            $access->respondNoAccess($this->owner);
        }
    }
}
