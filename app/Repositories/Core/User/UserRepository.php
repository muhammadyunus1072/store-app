<?php

namespace App\Repositories\Core\User;

use App\Helpers\MenuHelper;
use App\Models\Core\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;

class UserRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return User::class;
    }

    public static function search($search)
    {
        $data = User::select('id', 'name as text')
            ->when($search, function ($query) use ($search) {
                $query->where('name', env('QUERY_LIKE'), '%' . $search . '%');
            })
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        foreach ($data as $index => $item) {
            $data[$index]['id'] = Crypt::encrypt($item['id']);
        }

        return json_encode($data);
    }

    public static function findWithDetails($id)
    {
        return User::with('userCompanies')->where('id', $id)->first();
    }

    public static function update($id, $data)
    {
        $obj = self::find($id);

        MenuHelper::resetCacheByUser($id);

        return $obj->update($data);
    }

    public static function authenticatedUser(): User
    {
        return self::find(Auth::id());
    }

    public static function getByRole($roleId)
    {
        return User::whereHas('roles', function ($query) use ($roleId) {
            $query->whereId($roleId);
        })
            ->get();
    }

    public static function findByUsername($username)
    {
        return User::where('username', '=', $username)
            ->first();
    }

    public static function findByEmail($email)
    {
        return User::whereEmail($email)
            ->first();
    }

    public static function findByUsernameOrEmail($usernameOrEmail)
    {
        return User::where('username', '=', $usernameOrEmail)
            ->orWhere('email', '=', $usernameOrEmail)
            ->first();
    }

    public static function datatable($roleId)
    {
        return User::with('roles')
            ->when($roleId, function ($query) use ($roleId) {
                $query->whereHas('roles', function ($query) use ($roleId) {
                    $query->whereId($roleId);
                });
            });
    }
}
