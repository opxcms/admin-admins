<?php

namespace Modules\Admin\Admins\Controllers;

use Core\Foundation\Templater\Templater;
use Core\Http\Controllers\APIFormController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Admin\Admins\AdminAdmins;
use Modules\Admin\Authorization\AdminAuthorization;

class ManageAdminEditApiController extends APIFormController
{
    public $addCaption = 'admin_admins::manage.add_admin';
    public $editCaption = 'admin_admins::manage.edit_admin';
    public $create = 'manage/api/module/admin_admins/admin_edit/create';
    public $save = 'manage/api/module/admin_admins/admin_edit/save';
    public $redirect = '/admins/edit/';

    /**
     * Make admin add form.
     *
     * @return  JsonResponse
     */
    public function getAdd(): JsonResponse
    {
        if (!AdminAuthorization::can('admin_admins::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(AdminAdmins::path('Templates' . DIRECTORY_SEPARATOR . 'admin.php'));

        $template->fillDefaults();

        return $this->responseFormComponent(0, $template, $this->addCaption, $this->create);
    }

    /**
     * Make user add form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getEdit(Request $request): JsonResponse
    {
        if (
            !AdminAuthorization::can('admin_admins::view')
            && !AdminAuthorization::can('admin_admins::edit')
            && !AdminAuthorization::can('admin_managers::disable')
            && !AdminAuthorization::can('admin_managers::delete')
        ) {
            return $this->returnNotAuthorizedResponse();
        }

        $id = $request->input('id');
        $admins = AdminAdmins::getAdminsList();
        $admin = $admins[$id] ?? null;

        if ($admin === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $admin['id'] = $id;

        $template = $this->makeTemplate($admin, 'admin.php');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Create new user.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postCreate(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_admins::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(AdminAdmins::path('Templates' . DIRECTORY_SEPARATOR . 'admin.php'));
        $template->resolvePermissions();
        $template->fillValuesFromRequest($request);

        $email = $template->getEditableValues('email');

        $valid = $template->validate();

        if (!$this->checkEmailUnique($email)) {
            $valid = false;
            $template->addValidationError('email', trans('validation.unique', ['attribute' => 'email']));
        }

        if (!$valid) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $admin = $this->updateAdminData([], $values, true);

        // Refill template
        $template = $this->makeTemplate($admin, 'admin.php');

        return $this->responseFormComponent($admin['id'], $template, $this->editCaption, $this->save, $this->redirect . $admin['id']);
    }

    /**
     * Save user.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postSave(Request $request): JsonResponse
    {
        if (
            !AdminAuthorization::can('admin_admins::view')
            && !AdminAuthorization::can('admin_admins::edit')
            && !AdminAuthorization::can('admin_managers::disable')
            && !AdminAuthorization::can('admin_managers::delete')
        ) {
            return $this->returnNotAuthorizedResponse();
        }

        $id = $request->input('id');

        $admins = AdminAdmins::getAdminsList();
        $admin = $admins[$id] ?? null;

        if ($admin === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $template = new Templater(AdminAdmins::path('Templates' . DIRECTORY_SEPARATOR . 'admin.php'));
        $template->resolvePermissions();
        $template->fillValuesFromRequest($request);

        $email = $template->getEditableValues('email');

        $valid = $template->validate();

        if ($email !== null && !$this->checkEmailUnique($email, $id)) {
            $valid = false;
            $template->addValidationError('email', trans('validation.unique', ['attribute' => 'email']));
        }

        if (!$valid) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();
        $admin['id'] = $id;

        $admin = $this->updateAdminData($admin, $values);

        // Refill template
        $template = $this->makeTemplate($admin, 'admin.php');

        return $this->responseFormComponent($admin['id'], $template, $this->editCaption, $this->save);
    }

    /**
     * Check if email is unique.
     *
     * @param string $email
     * @param int|null $except
     *
     * @return  bool
     */
    protected function checkEmailUnique(string $email, int $except = null): bool
    {
        $admins = AdminAdmins::getAdminsList();
        $emails = array_column($admins, 'email');

        if ($except !== null) {
            unset($emails[$except]);
        }

        return !in_array($email, $emails, true);
    }

    /**
     * Fill template with data.
     *
     * @param string $filename
     * @param array $admin
     *
     * @return  Templater
     */
    protected function makeTemplate(array $admin, $filename): Templater
    {
        $template = new Templater(AdminAdmins::path('Templates' . DIRECTORY_SEPARATOR . $filename));

        $template->fillValuesFromArray($admin);
        $template->setValues(['password' => '']);

        return $template;
    }

    /**
     * Update user's data
     *
     * @param array $admin
     * @param array $data
     * @param bool $new
     *
     * @return  array
     */
    protected function updateAdminData(array $admin, array $data, bool $new = false): array
    {
        // Check for password
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else if ($new) {
            $data['password'] = bcrypt(str_random(8));
        } else {
            unset($data['password']);
        }

        if (empty($data['blocked'])) {
            $data['blocked'] = false;
        }
        if (empty($data['deleted'])) {
            $data['deleted'] = false;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, ['email', 'name', 'blocked', 'deleted', 'password'], true)) {
                $admin[$key] = $data[$key];
            }
        }

        $admin = AdminAdmins::saveAdmin($admin);

        return $admin;
    }
}