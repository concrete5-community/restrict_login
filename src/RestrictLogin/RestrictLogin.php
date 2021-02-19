<?php 
namespace Concrete\Package\RestrictLogin\Src\RestrictLogin;

use Concrete\Core\Utility\IPAddress;
use Config;
use Core;
use Redirect;
use Symfony\Component\HttpFoundation\IpUtils;

class RestrictLogin
{
    /**
     * Log out the user if the IP isn't allowed.
     *
     * @param \Concrete\Core\User\Event\User $eu
     *
     * @return bool
     */
    public function onUserLogin($eu)
    {
        $user_ip = $this->getUserIP();
        $ips = $this->getAllowedIPs();

        // Stop processing, no IP address has been added yet.
        if (count($ips) === 0) {
            return false;
        }

        // Check if user's IP is allowed
        if (!$this->checkIP($user_ip, $ips)) {
            $u = $eu->getUserObject();

            $u->logout();

            Redirect::to('/login')->send();
        }
    }

    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of given IPs or subnets.
     *
     * @param string $user_ip
     * @param array $ips
     *
     * @return bool
     */
    public function checkIP($user_ip, $ips)
    {
        return IpUtils::checkIp($user_ip, $ips);
    }

    /**
     * Return array with allowed IP addresses.
     *
     * @return array
     */
    public function getAllowedIPs()
    {
        $ips_config = Config::get('restrict_login.ips');

        return !is_array($ips_config) ? array() : array_keys($ips_config);
    }

    /**
     * Return the user's IP address.
     *
     * return string
     */
    public function getUserIP()
    {
        $iph = Core::make('helper/validation/ip');
        $ip = $iph->getRequestIP();

        return $ip->getIP(IPAddress::FORMAT_IP_STRING);
    }
}
