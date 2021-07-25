<?php

namespace Snowdog\Academy\Menu;

class AddFundsMenu extends AbstractMenu
{
    public function getHref(): string
    {
        return '/account/addfunds';
    }

    public function getLabel(): string
    {
        return 'Add Funds';
    }

    public function isVisible(): bool
    {
        return !!$_SESSION['login'];
    }
}