<?php

namespace App\Services;

use App\Models\User;
use App\Models\Club;
use App\Models\Team;
use App\Models\Squad;
use App\Models\Location;
use App\Models\Subscription;
use App\Models\Training;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataTransferService
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

    /**
     * Export data based on type.
     */
    public function exportData(string $type)
    {
        $data = [];
        if ($type === 'all') {
            foreach ($this->models as $key => $modelClass) {
                $data[$key] = $modelClass::all();
            }
            $data['pivot_parent_student'] = DB::table('parent_student')->get();
            $data['pivot_squad_user'] = DB::table('squad_user')->get();
            $data['pivot_team_user'] = DB::table('team_user')->get();
        }
        elseif (isset($this->models[$type])) {
            $data[$type] = $this->models[$type]::all();

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

        return $data;
    }

    /**
     * Import data from array.
     */
    public function importData(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Process main models
            foreach ($this->models as $key => $modelClass) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $item) {
                        $id = $item['id'] ?? null;
                        $itemData = $item;

                        if ($key === 'users' && !isset($itemData['password'])) {
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

                        if (!DB::table($config['table'])->where($where)->exists()) {
                            DB::table($config['table'])->insert((array)$item);
                        }
                    }
                }
            }

            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
