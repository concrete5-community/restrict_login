<?php

namespace Concrete\Package\RestrictLogin;

use A3020\RestrictLogin\OnLoginListener;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Controller extends Package
{
    protected $pkgHandle = 'restrict_login';
    protected $appVersionRequired = '8.5.0';
    protected $pkgVersion = '2.0.0';
    protected $pkgAutoloaderRegistries = [
        'src/RestrictLogin' => '\A3020\RestrictLogin',
    ];

    public function getPackageName()
    {
        return t('Restrict Login');
    }

    public function getPackageDescription()
    {
        return t('Only allow login from certain IP addresses.');
    }

    public function on_start()
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->app->make(EventDispatcherInterface::class);

        // Triggered in /concrete/controllers/single_page/login.php
        $dispatcher->addListener('on_user_login', function($event) {
            /** @var \A3020\RestrictLogin\OnLoginListener $listener */
            $listener = $this->app->make(OnLoginListener::class);
            $listener->onLogin($event);
        });
    }

    public function install()
    {
        $pkg = parent::install();

        foreach ([
            '/dashboard/system/registration/restrict_login' => [
                'cName' => 'Restrict Login',
            ],
        ] as $path => $value) {
            if (!is_array($value)) {
                $path = $value;
                $value = array();
            }
            $page = Page::getByPath($path);
            if (!$page || $page->isError()) {
                $single_page = Single::add($path, $pkg);

                if ($value) {
                    $single_page->update($value);
                }
            }
        }
    }
}
