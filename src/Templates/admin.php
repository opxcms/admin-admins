<?php

namespace Modules\Opx\Users\Templates;

use Core\Foundation\Template\Template;

/**
 * HELP:
 *
 * ID parameter is shorthand for defining module and field name separated by `::`.
 * [$module, $name] = explode('::', $id, 2);
 * $captionKey = "{$module}::template.section_{$name}";
 *
 * PLACEMENT is shorthand for section and group of field separated by `/`.
 * [$section, $group] = explode('/', $placement);
 *
 * PERMISSIONS is shorthand for read permission and write permission separated by `|`.
 * [$readPermission, $writePermission] = explode('|', $permissions, 2);
 */

return [
    'sections' => [
        Template::section('general'),
    ],
    'groups' => [
        Template::group('common'),
    ],
    'fields' => [
        Template::string('id', 'general/common', '', [], 'fields.id_info', '', 'admin_admins::view|none'),
        Template::string('admin_admins::name', 'general/common', '', [], '', '', 'admin_admins::view|admin_admins::edit'),
        Template::string('email', 'general/common', '', [], '', 'required|email', 'admin_admins::view|admin_admins::edit'),
        Template::string('password', 'general/common', '', [], 'fields.info_password', 'nullable|min:6', 'admin_admins::view|admin_admins::edit'),
        Template::checkbox('admin_admins::blocked', 'general/common', false, 'admin_admins::template.info_blocked', '', 'admin_admins::view|admin_admins::disable'),
        Template::checkbox('admin_admins::deleted', 'general/common', false, '', '', 'admin_admins::view|admin_admins::delete'),
    ],
];
