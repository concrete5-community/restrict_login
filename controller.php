<?php 
namespace Concrete\Package\RestrictLogin;

use Concrete\Package\RestrictLogin\Src\RestrictLogin\RestrictLogin;
use Events;
use Package;
use Page;
use SinglePage;

class Controller extends Package
{
    protected $pkgHandle = 'restrict_login';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';

    protected $single_pages = array(
        '/dashboard/system/registration/restrict_login' => array(
            'cName' => 'Restrict Login',
        ),
    );

    public function getPackageName()
    {
        return t("Restrict Login");
    }

    public function getPackageDescription()
    {
        return t("Only allow login from certain IP addresses.");
    }

    public function on_start()
    {
        // Triggered in /concrete/controllers/single_page/login.php
        Events::addListener('on_user_login', function($eu) {
            $rl = new RestrictLogin();
            $rl->onUserLogin($eu);
        });
    }

    public function install()
    {
        $pkg = parent::install();

        $this->installPages($pkg);
    }

    /**
     * @param Package $pkg
     */
    protected function installPages($pkg)
    {
        foreach ($this->single_pages as $path => $value) {
            if (!is_array($value)) {
                $path = $value;
                $value = array();
            }
            $page = Page::getByPath($path);
            if (!$page || $page->isError()) {
                $single_page = SinglePage::add($path, $pkg);

                if ($value) {
                    $single_page->update($value);
                }
            }
        }
    }
}
