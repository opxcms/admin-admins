<?php

namespace Modules\Admin\Admins\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Core\Http\Controllers\APIListController;
use Modules\Admin\Admins\AdminAdmins;
use Modules\Admin\Authorization\AdminAuthorization;

class ManageAdminsListApiController extends APIListController
{
    protected $caption = 'admin_admins::manage.admins';
    protected $source = 'manage/api/module/admin_admins/admins_list/admins';

    protected $filters = [
        'blocked' => [
            'caption' => 'admin_admins::manage.filter_by_blocked',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'yes',
            'options' => ['yes' => 'admin_admins::manage.filter_value_yes', 'no' => 'admin_admins::manage.filter_value_no'],
        ],
        'show_deleted' => [
            'caption' => 'admin_admins::manage.filter_by_deleted',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'yes',
            'options' => ['yes' => 'admin_admins::manage.filter_value_yes', 'only_deleted' => 'admin_admins::manage.filter_by_only_deleted'],
        ],
    ];

    /**
     * Returns list component with associated settings.
     *
     * @return  JsonResponse
     */
    public function getIndex(): JsonResponse
    {
        if(!AdminAuthorization::can('admin_admins::list')) {
            return $this->returnNotAuthorizedResponse();
        }

        return $this->responseListComponent();
    }

    /**
     * Get list of admins with filters.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postAdmins(Request $request): JsonResponse
    {
        if(!AdminAuthorization::can('admin_admins::list')) {
            return $this->returnNotAuthorizedResponse();
        }

        $showBlocked = $request->input('filters.blocked');
        $showDeleted = $request->input('filters.show_deleted');

        $admins = [];

        foreach (AdminAdmins::getAdminsList() as $key => $admin) {
            $isBlocked = $admin['blocked'] ?? false;
            $isDeleted = $admin['deleted'] ?? false;

            if (($showBlocked === null || ($showBlocked === 'yes' && $isBlocked) || ($showBlocked === 'no' && !$isBlocked)) &&
                (($showDeleted === null && $isDeleted === false) || ($showDeleted === 'yes') || ($showDeleted === 'only_deleted' && $isDeleted))) {
                $admin['id'] = $key;
                $admin['blocked'] = $isBlocked;
                $admin['deleted'] = $isDeleted;
                $admins[] = $this->formatAdmin($admin);
            }
        }

        return response()->json(['data' => $admins]);
    }

    /**
     * Format admin record for displaying in list.
     *
     * @param array $admin
     *
     * @return  array
     */
    protected function formatAdmin(array $admin): array
    {
        return $this->makeListRecord(
            $admin['id'],
            $admin['email'],
            null,
            null,
            $admin['name'],
            empty($admin['blocked']),
            !empty($admin['deleted'])
        );
    }

    /**
     * Make add link if can.
     *
     * @return  string|null
     */
    protected function getAddLink(): ?string
    {
        return AdminAuthorization::can('admin_admins::add') ? 'admin_admins::admins_add' : null;
    }

    /**
     * Make edit link if can.
     *
     * @return  string|null
     */
    protected function getEditLink(): ?string
    {
        if (
            !AdminAuthorization::can('admin_admins::view')
            && !AdminAuthorization::can('admin_admins::edit')
            && !AdminAuthorization::can('admin_managers::disable')
            && !AdminAuthorization::can('admin_managers::delete')
        ) {
            return null;
        }

        return 'admin_admins::admins_edit';
    }

}