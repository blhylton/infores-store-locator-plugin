<?php
/**
 * Created by PhpStorm.
 * User: Barry Hylton
 * Date: 3/17/2019
 * Time: 12:43 AM
 */

namespace BLHylton\InfoResStoreLocator\WordPress;


class WPInit
{
    public static function init()
    {
        $admin = new WPAdmin();
        $admin->init();

        $api = new WPAPI();
        $api->init();
    }

}