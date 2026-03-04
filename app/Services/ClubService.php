<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Location;
use App\Models\Subscription;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubService
{
    /**
     * List clubs with filtering.
     */
    public function listClubs(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'administrator') {
            return Club::with('creator')->latest()->get();
        }
        return Club::where('id', $user->club_id)->with('creator')->latest()->get();
    }

    /**
     * Create a new club.
     */
    public function createClub(array $data, string $creatorId)
    {
        $data['created_by'] = $creatorId;
        return Club::create($data);
    }

    /**
     * Update a club.
     */
    public function updateClub(Club $club, array $data)
    {
        $club->update($data);
        return $club;
    }

    /**
     * Check if a club can be deleted.
     */
    public function canDeleteClub(Club $club): ?string
    {
        if ($club->users()->count() > 0) {
            return 'Acest club nu poate fi șters deoarece are utilizatori asociați. Transferați sau ștergeți utilizatorii mai întâi.';
        }

        if ($club->teams()->count() > 0) {
            return 'Acest club nu poate fi șters deoarece are grupe (echipe) asociate. Ștergeți grupele mai întâi.';
        }

        if (Location::where('club_id', $club->id)->exists()) {
            return 'Acest club are locații asociate. Ștergeți locațiile mai întâi.';
        }

        if (Subscription::where('club_id', $club->id)->exists()) {
            return 'Acest club are definiții de abonamente alocate. Ștergeți-le mai întâi.';
        }

        return null;
    }

    /**
     * List locations with filtering.
     */
    public function listLocations(Request $request)
    {
        $user = $request->user();
        $query = Location::with('club');

        if ($user->role === 'manager') {
            $query->where('club_id', $user->club_id);
        }
        elseif ($user->role === 'administrator') {
            if ($request->filled('club_id')) {
                $query->where('club_id', $request->club_id);
            }
        }

        return $query->get();
    }

    /**
     * Create a new location.
     */
    public function createLocation(array $data)
    {
        return Location::create($data);
    }

    /**
     * Update a location.
     */
    public function updateLocation(Location $location, array $data)
    {
        $location->update($data);
        return $location;
    }

    /**
     * Check if a location can be deleted.
     */
    public function canDeleteLocation(Location $location): ?string
    {
        if (Training::where('location_id', $location->id)->exists()) {
            return 'Această locație este folosită de unul sau mai multe antrenamente și nu poate fi ștearsă.';
        }
        return null;
    }
}
