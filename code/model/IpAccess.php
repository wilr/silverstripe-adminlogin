<?php

/**
 * Check Access based on remote IP address
 *
 * @Example entries :
 * 192.168.178.8
 * 192.168.178.0/24
 * 192.168.178.0-50
 * 192.168.178.*
 * 192.168.*
 * @return false || string : entry the ip address was matched against
 */
class IpAccess
{
    /**
     * @var array
     */
    public $allowedIps = array();

    /**
     * @var string
     */
    private $ip = '';

    /**
     * IpAccess constructor.
     *
     * @param string $ip
     * @param array $allowedIps
     */
    public function __construct($ip = '', $allowedIps = array())
    {
        $this->ip         = $ip;
        $this->allowedIps = $allowedIps;
    }

    /**
     * @param $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function hasAccess()
    {
        if (empty($this->allowedIps)) {
            return 'allowed';
        } elseif ($match = $this->matchExact()) {
            return $match;
        } elseif ($match = $this->matchRange()) {
            return $match;
        } elseif ($match = $this->matchCIDR()) {
            return $match;
        } elseif ($match = $this->matchWildCard()) {
            return $match;
        }
    }

    /**
     * @return string|null
     */
    public function matchExact()
    {
        if (in_array($this->ip, $this->allowedIps)) {
            return $this->ip;
        }
    }

    /**
     * try to match against a ip range
     * Example : 192.168.1.50-100
     */
    public function matchRange()
    {
        if ($ranges = array_filter($this->allowedIps, function ($ip) {
            return strstr($ip, '-');
        })
        ) {
            foreach ($ranges as $range) {
                $first = substr($range, 0, strrpos($range, '.') + 1);
                $last  = substr(strrchr($range, '.'), 1);
                list ($start, $end) = explode('-', $last);
                for ($i = $start; $i <= $end; $i++) {
                    if ($this->ip === $first . $i) {
                        return $range;
                    }
                }
            }
        }
    }

    /**
     * try to match cidr range
     * Example : 192.168.1.0/24
     */
    public function matchCIDR()
    {
        if ($ranges = array_filter($this->allowedIps, function ($ip) {
            return strstr($ip, '/');
        })
        ) {
            foreach ($ranges as $cidr) {
                list ($net, $mask) = explode('/', $cidr);
                if ((ip2long($this->ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($net)) {
                    return $cidr;
                }
            }
        }
    }

    /**
     * try to match against a range that ends with a wildcard *
     * Example : 192.168.1.*
     * Example : 192.168.*
     */
    public function matchWildCard()
    {
        if ($ranges = array_filter($this->allowedIps, function ($ip) {
            return substr($ip, -1) === '*';
        })
        ) {
            foreach ($ranges as $range) {
                if (substr($this->ip, 0, strlen(substr($range, 0, -1))) === substr($range, 0, -1)) {
                    return $range;
                }
            }
        }
    }

}
