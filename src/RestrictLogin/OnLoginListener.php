<?php

namespace A3020\RestrictLogin;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Utility\IPAddress;
use Symfony\Component\HttpFoundation\IpUtils;

class OnLoginListener implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Log out the user if the IP isn't allowed.
     *
     * @param \Concrete\Core\User\Event\User $event
     *
     * @return null
     */
    public function onLogin($event)
    {
        $user_ip = $this->getUserIP();
        $ips = $this->getAllowedIPs();

        // No IP address has been added yet, allow login from anywhere.
        if (count($ips) === 0) {
            return;
        }

        // Check if user's IP is allowed
        if (!$this->checkIP($user_ip, $ips)) {
            $u = $event->getUserObject();

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
    private function checkIP($user_ip, $ips)
    {
        return IpUtils::checkIp($user_ip, $ips);
    }

    /**
     * Return array with allowed IP addresses.
     *
     * @return array
     */
    private function getAllowedIPs()
    {
        $ips_config = $this->config->get('restrict_login.ips');

        return !is_array($ips_config) ? array() : array_keys($ips_config);
    }

    /**
     * Return the user's IP address.
     *
     * @return string
     */
    private function getUserIP()
    {
        $iph = $this->app->make('helper/validation/ip');
        $ip = $iph->getRequestIP();

        return $ip->getIP(IPAddress::FORMAT_IP_STRING);
    }
}
