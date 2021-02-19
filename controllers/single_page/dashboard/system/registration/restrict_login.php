<?php  
namespace Concrete\Package\RestrictLogin\Controller\SinglePage\Dashboard\System\Registration;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\Request;
use Concrete\Core\Http\Response;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Utility\IPAddress;
use Concrete\Core\View\View;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class RestrictLogin extends DashboardPageController
{
    public function view()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $this->set('ips', $config->get('restrict_login.ips', []));
    }

    /**
     * Render dialog add_ip.
     */
    public function modify_dialog()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $req = $this->app->make(Request::class);
        $iph = $this->app->make('helper/validation/ip');
        $ip = $iph->getRequestIP();

        $entry = [
            'old_ip' => null,
            'description' => null,
        ];

        $view = new View('modify_ip_dialog');
        $view->addScopeItems(array(
            'current_ip' => $ip->getIP(IPAddress::FORMAT_IP_STRING),
            'token' => $this->token,
        ));
        $view->setPackageHandle('restrict_login');

        if (!empty($req->post('ip'))) {
            $ip = $req->post('ip');
            $ips = $config->get('restrict_login.ips', []);
            if (isset($ips[$ip])) {
                $entry = $ips[$ip];
                $entry['old_ip'] = $ip;
                $entry['ip'] = $ip;
            }
        }

        $view->addScopeItems(array('entry' => $entry));

        $response = new Response($view->render());
        $response->send();

        $this->app->shutdown();
    }

    /**
     * Handle POST data from modify_ip dialog.
     */
    public function modify()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $json = ['error' => null, 'message' => null];
        $req = Request::getInstance();
        $ips = $config->get('restrict_login.ips');
        $sec = $this->app->make('helper/security');

        try {
            $token = trim($req->get('token'));
            if (!$this->token->validate('restrict_login::ip.modify', $token)) {
                throw new Exception(t('Invalid token'));
            }

            $ip = trim($req->get('ip'));

            if (
                (!$req->post('old_ip') && isset($ips[$ip])) ||
                ($req->post('old_ip') != $ip && isset($ips[$ip]))
            ) {
                throw new Exception(t('This IP address already exists'));
            }

            if (preg_match("/[^0-9\.:\/A-Za-z]/", $ip) !== 0) {
                throw new Exception(t('Invalid IP address'));
            }

            $description = $sec->sanitizeString($req->get('description'));

            $ips[$ip] = array(
                'description' => $description,
            );

            // If IP has changed in modify dialog, delete the old entry.
            $old_ip = $req->get('old_ip');
            if ($old_ip && $old_ip !== $ip) {
                if (isset($ips[$old_ip])) {
                    unset($ips[$old_ip]);
                }
            }

            $config->save('restrict_login.ips', $ips);
        } catch (Exception $e) {
            $json['error'] = true;
            $json['message'] = $e->getMessage();
        }

        $response = new JsonResponse($json);
        $response->send();

        $this->app->shutdown();
    }

    public function add_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully added.'));
    }

    public function update_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully updated.'));
    }

    /**
     * Delete IP entry.
     */
    public function delete()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);

        $json = array('error' => null, 'message' => null);
        $req = Request::getInstance();

        $token = trim($req->get('token'));
        $ip = trim($req->get('ip'));
        if ($this->token->validate("restrict_login::modify.{$ip}", $token)) {
            if ($ip) {
                $ips = $config->get('restrict_login.ips');

                if (isset($ips[$ip])) {
                    unset($ips[$ip]);

                    $config->save('restrict_login.ips', $ips);
                }
            } else {
                $json['error'] = true;
                $json['message'] = t('Invalid request');
            }
        } else {
            $json['error'] = true;
            $json['message'] = t('Invalid token');
        }

        $response = new JsonResponse($json);
        $response->send();

        $this->app->shutdown();
    }

    public function delete_success()
    {
        $this->view();
        $this->set('message', t('IP address successfully deleted.'));
    }

    public function save_success()
    {
        $this->set('message', t('Settings saved'));
    }
}
