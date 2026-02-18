<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\DataTables\SettingDataTable;
use App\Services\Admin\SettingServices;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponses;

    public $settingServices;
    public $viewData;

    public function __construct(SettingServices $settingServices)
    {
        $this->settingServices = $settingServices;
        $this->viewData = [
            'title'          => 'Manage Settings',
            'permission'     => 'setting',
            'prefix'         => 'setting_',
            'dataTableID'    => 'setting-table',
            'canvasId'       => 'manage-record',
            'canvasSize'     => 'canvas-sm',
            'canvasHeading'  => 'Manage Setting',
            'deleteRoute'    => route('admin.delete.setting'),
            'manageRoute'    => route('admin.manage.setting'),
        ];
    }

    /**
     * Display settings list via DataTable.
     */
    public function index(SettingDataTable $dataTable)
    {
        return $dataTable->render('admin.settings.index', ['viewData' => $this->viewData]);
    }

    /**
     * Load manage setting canvas (create/edit).
     */
    public function manage_setting(Request $request)
    {
        return $this->settingServices->manage_setting_service($request);
    }

    /**
     * Save setting (create or update).
     */
    public function save_setting(Request $request)
    {
        return $this->settingServices->save_setting_service($request);
    }

    /**
     * Delete a setting.
     */
    public function delete_setting(Request $request)
    {
        return $this->settingServices->delete_setting_service($request);
    }
}
