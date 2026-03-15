<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public $viewData = [];

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->viewData = [
            'title' => 'Users',
            'dataTableID' => 'user-table',
            'canvasId' => 'manage-record',
            'canvasHeading' => 'Manage User',
            'deleteRoute' => route('admin.users.delete'),
            'manageRoute' => route('admin.users.manage'),
        ];
    }

    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index', ['viewData' => $this->viewData]);
    }

    public function manage_user(Request $request)
    {
        return $this->userService->manage_user_service($request);
    }

    public function save_user(Request $request)
    {
        return $this->userService->save_user_service($request);
    }

    public function delete_user(Request $request)
    {
        return $this->userService->delete_user_service($request);
    }

    public function export(): StreamedResponse
    {
        $filename = 'users_' . date('Y-m-d_His') . '.csv';
        $headers = ['#', 'Android ID', 'VIP', 'Video Clicks', 'Registered'];
        $query = User::query()->orderBy('created_at', 'desc');

        return response()->streamDownload(function () use ($headers, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $i = 0;
            foreach ($query->cursor() as $row) {
                $i++;
                fputcsv($handle, [
                    $i,
                    $row->android_id,
                    $row->is_vip ? 'Yes' : 'No',
                    $row->video_click_count ?? 0,
                    $row->created_at ? $row->created_at->format('d M Y H:i') : '-',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
