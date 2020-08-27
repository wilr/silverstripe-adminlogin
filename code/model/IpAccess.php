<?php

/**
 * Check Access based on remote IP address.
 *
 * @Example entries :
 * 192.168.178.8
 * 192.168.178.0/24
 * 192.168.178.0-50
 * 192.168.178.*
 * 192.168.*
 *
 * @return false || string : entry the ip address was matched against
 */
class IpAccess extends SS_Object
{
    /**
     * @var array
     */
    public $allowedIps = [];

    /**
     * @config
     *
     * @var array
     */
    private static $allowed_ips = [];

    /**
     * @var string
     */
    private $ip = '';

    /**
     * IpAccess constructor.
     *
     * @param string $ip
     * @param array  $allowedIps
     */
    public function __construct($ip = '', $allowedIps = [])
    {
        parent::__construct();
        $this->ip = $ip;

        self::config()->allowed_ips = $allowedIps;
    }

    /**
     * @param $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return array
     */
    public function getAllowedIps()
    {
        if (!empty($this->allowedIps)) {
            Deprecation::notice('1.1', 'Use the "IpAccess.allowed_ips" config setting instead');
            self::config()->allowed_ips = $this->allowedIps;
        }

        return self::$allowed_ips ? self::$allowed_ips : (array) self::config()->allowed_ips;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Config::inst()->get('IpAccess', 'enabled');
    }

    /**
     * @return bool
     */
    public function hasAccess()
    {
        if (!$this->isEnabled() || !(bool) $this->getAllowedIps()) {
            return true;
        }

        return $this->matchIp();
    }

    /**
     * @return bool
     */
    public function matchIp()
    {
        return $this->matchExact() || $this->matchRange() || $this->matchCIDR() || $this->matchWildCard();
    }

    /**
     * @param Controller $controller
     *
     * @throws SS_HTTPResponse_Exception
     */
    public function respondNoAccess(Controller $controller)
    {
        $response = null;
        if (class_exists('ErrorPage', true)) {
            $response = ErrorPage::response_for(403);
        }
        $controller->httpError(403, $response ? $response : 'The requested page could not be found.');
    }

    /**
     * @return string
     */
    public function matchExact()
    {
        return in_array($this->ip, $this->getAllowedIps()) ? $this->ip : '';
    }

    /**
     * Try to match against a ip range
     * Example : 192.168.1.50-100.
     *
     * @return string
     */
    public function matchRange()
    {
        $ranges = array_filter($this->getAllowedIps(), function ($ip) {
            return strstr($ip, '-');
        });

        $ip = $this->ip;

        $matches = array_filter($ranges, function ($range) use ($ip) {
            $ipFirstPart = substr($ip, 0, strrpos($ip, '.') + 1);
            $ipLastPart = substr(strrchr($ip, '.'), 1);
            $rangeFirstPart = substr($range, 0, strrpos($range, '.') + 1);

            list($start, $end) = explode('-', substr(strrchr($range, '.'), 1));

            return $ipFirstPart === $rangeFirstPart && $ipLastPart >= $start && $ipLastPart <= $end;
        });

        return array_shift($matches);
    }

    /**
     * Try to match cidr range
     * Example : 192.168.1.0/24.
     *
     * @return string
     */
    public function matchCIDR()
    {
        $ranges = array_filter($this->getAllowedIps(), function ($ip) {
            return strstr($ip, '/');
        });

        if (!empty($ranges)) {
            foreach ($ranges as $range) {
                list($net, $mask) = explode('/', $range);
                if ((ip2long($this->ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($net)) {
                    return $range;
                }
            }
        }

        return '';
    }

    /**
     * Try to match against a range that ends with a wildcard *
     * Example : 192.168.1.*
     * Example : 192.168.*.
     *
     * @return string
     */
    public function matchWildCard()
    {
        $ranges = array_filter($this->getAllowedIps(), function ($ip) {
            return substr($ip, -1) === '*';
        });

        if (!empty($ranges)) {
            foreach ($ranges as $range) {
                if (substr($this->ip, 0, strlen(substr($range, 0, -1))) === substr($range, 0, -1)) {
                    return $range;
                }
            }
        }

        return '';
    }
}
