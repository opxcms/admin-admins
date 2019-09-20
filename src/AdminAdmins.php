<?php

namespace Modules\Admin\Admins;

use Illuminate\Support\Facades\Facade;

/**
 * @method  static array getAdminsList()
 * @method  static array saveAdmin(array $admin)
 * @method  static string name()
 * @method  static string get($key)
 * @method  static string path($path = '')
 * @method  static array|string|null  config($key = null)
 * @method  static mixed view($view)
 */
class AdminAdmins extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'admin_admins';
    }
}
