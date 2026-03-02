<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Club;
use App\Models\Team;
use App\Models\Squad;
use App\Models\Location;
use App\Models\Subscription;
use App\Models\Training;
use App\Models\UserSubscription;

class ExportImportController extends Controller
{
    protected $models = [
        'clubs' => Club::class ,
        'users' => User::class ,
        'teams' => Team::class ,
        'squads' => Squad::class ,
        'locations' => Location::class ,
        'subscriptions' => Subscription::class ,
        'trainings' => Training::class ,
        'user-subscriptions' => UserSubscription::class ,
    ];

    public function export(Request $request, $type)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = [];
        if ($type === 'all') {
            foreach ($this->models as $key => $modelClass) {
                $data[$key] = $modelClass::all();
            }
            // Add pivot table data for many-to-many relationships
            $data['pivot_parent_student'] = DB::table('parent_student')->get();
            $data['pivot_squad_user'] = DB::table('squad_user')->get();
            $data['pivot_team_user'] = DB::table('team_user')->get();
        }
        elseif (isset($this->models[$type])) {
            $data[$type] = $this->models[$type]::all();

            // Include relevant pivot data if type-specific
            if ($type === 'users') {
                $data['pivot_parent_student'] = DB::table('parent_student')->get();
                $data['pivot_squad_user'] = DB::table('squad_user')->get();
                $data['pivot_team_user'] = DB::table('team_user')->get();
            }
            elseif ($type === 'squads') {
                $data['pivot_squad_user'] = DB::table('squad_user')->get();
            }
            elseif ($type === 'teams') {
                $data['pivot_team_user'] = DB::table('team_user')->get();
            }
        }
        else {
            return response()->json(['message' => 'Invalid export type'], 400);
        }

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="export_' . $type . '_' . now()->format('Y-m-d_H-i-s') . '.json"',
        ]);
    }

    public function import(Request $request)
    {
        if ($request->user()->role !== 'administrator') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:json,txt',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (!$data) {
            return response()->json(['message' => 'Invalid JSON file'], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Process main models
            foreach ($this->models as $key => $modelClass) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $item) {
                        $id = $item['id'] ?? null;

                        // Prepare data (remove sensitive or auto-gen fields if needed)
                        $itemData = $item;
                        if ($key === 'users' && !isset($itemData['password'])) {
                            // Only set password for new users
                            if (!$id || !User::where('id', $id)->exists()) {
                                $itemData['password'] = Hash::make(Str::random(12));
                            }
                        }

                        if ($id) {
                            $modelClass::updateOrCreate(['id' => $id], $itemData);
                        }
                        else {
                            $modelClass::create($itemData);
                        }
                    }
                }
            }

            // 2. Process pivot tables
            $pivots = [
                'pivot_parent_student' => ['table' => 'parent_student', 'keys' => ['parent_id', 'student_id']],
                'pivot_squad_user' => ['table' => 'squad_user', 'keys' => ['squad_id', 'user_id']],
                'pivot_team_user' => ['table' => 'team_user', 'keys' => ['team_id', 'user_id']],
            ];

            foreach ($pivots as $key => $config) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $item) {
                        $where = [];
                        foreach ($config['keys'] as $k) {
                            $where[$k] = $item[$k];
                        }

                        // Check if exists to avoid duplicates
                        if (!DB::table($config['table'])->where($where)->exists()) {
                            DB::table($config['table'])->insert((array)$item);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Import completed successfully!']);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
