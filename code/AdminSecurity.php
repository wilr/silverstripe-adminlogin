<?php


/**
 * Class AdminSecurity
 */
class AdminSecurity extends Security
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'passwordsent',
        'ChangePasswordForm'
    );

    /**
     * Template thats used to render the pages.
     *
     * @config
     * @var string
     */
    private static $template_main = 'AdminLogin';

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        if (Config::inst()->get('IpAccess', 'enabled')) {
            $ipAccess = new IpAccess($this->getRequest()->getIP(),
                Config::inst()->get('IpAccess', 'allowed_ips'));
            if (!$ipAccess->hasAccess()) {
                $response = null;
                if (class_exists('ErrorPage', true)) {
                    $response = ErrorPage::response_for(404);
                }
                $this->httpError(404, $response ? $response : 'The requested page could not be found.');
                return;
            }
        }

        if (Config::inst()->get('AdminLogin', 'UseTheme') !== true) {
            // this prevents loading frontend css and javscript files
            Object::useCustomClass('Page_Controller', 'AdminLoginPage_Controller');
            Requirements::css('adminlogin/css/style.css');
        }

        Object::useCustomClass('MemberLoginForm', 'AdminLoginForm');
    }

    /**
     * @param null $action
     * @return string
     */
    public function Link($action = null)
    {
        return "AdminSecurity/$action";
    }

    /**
     * @return string
     */
    public static function isAdminLogin()
    {
        return strstr(self::getBackUrl(), '/admin/');
    }

    /**
     * @return string
     */
    public static function getBackUrl()
    {
        $request = Controller::curr()->getRequest();
        if ($url = $request->requestVar('BackURL')) {
            return $url;
        }
        return '';
    }

    /**
     * @param SS_HTTPRequest $request
     * @return string
     */
    public function passwordsent($request)
    {
        return parent::passwordsent($request);
    }

    /**
     * @see Security::getPasswordResetLink()
     * We overload this, so we can add the BackURL to the password resetlink
     * @param Member $member
     * @param string $autologinToken
     * @return string
     */
    public static function getPasswordResetLink($member, $autologinToken)
    {
        $autologinToken      = urldecode($autologinToken);
        $selfControllerClass = __CLASS__;
        $selfController      = new $selfControllerClass();
        return $selfController->Link('changepassword') . "?m={$member->ID}&t=$autologinToken";
    }

    /**
     * @return ChangePasswordForm
     */
    public function ChangePasswordForm()
    {
        return new ChangePasswordForm($this, 'ChangePasswordForm');
    }
}
