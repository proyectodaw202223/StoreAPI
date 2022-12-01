<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\NotFoundException;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\UpdateConflictException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\RestrictedDeletionException;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createUser(array $userData): User {
        self::validateUserDataOnCreate($userData);
        $user = User::create($userData);
        $user->emptyPasswordForDataProtection();

        return $user;
    }

    public static function validateUserDataOnCreate(array $userData): void {
        self::validateRequiredDataIsSetOnCreate($userData);
        self::validateIfUserAlreadyExistsOnCreate($userData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $userData): void {
        if (!isset($userData['email']) ||
            !isset($userData['password']) ||
            !isset($userData['role'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateIfUserAlreadyExistsOnCreate(array $userData): void {
        $user = User::findByEmail($userData['email']);

        if ($user->id) {
            throw new ResourceAlreadyExistsException();
        }
    }

    public static function findByEmail(string $email): User {
        $user = DB::table('users')
            ->where('email', '=', $email)
            ->first();

        $user = User::hydrate([$user])[0];
        $user->emptyPasswordForDataProtection();
        
        return $user;
    }

    public function emptyPasswordForDataProtection() {
        $this->password = "";
    }

    public static function updateUser(array $userData, User $user): User {
        $userData = self::getCustomerDataWithOriginalPasswordIfEmpty($userData, $user);
        self::validateUserDataOnUpdate($userData, $user);
        $user->update($userData);
        $user->emptyPasswordForDataProtection();

        return $user;
    }

    private static function getCustomerDataWithOriginalPasswordIfEmpty(
        array $userData, User $originalUser): array {
        
        if (key_exists('password', $userData) && $userData['password'] == "") {
            $userData['password'] = $originalUser->password;
        }

        return $userData;
    }

    public static function validateUserDataOnUpdate(array $userData, User $user): void {
        self::validateRequiredDataIsSetOnUpdate($userData);
        self::validateUpdateConflict($userData, $user);
        self::validateInmutableFieldsDidNotChange($userData, $user);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $userData): void {
        if (!isset($userData['id']) ||
            !isset($userData['email']) ||
            !isset($userData['password']) ||
            !isset($userData['role']) ||
            !isset($userData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $userData, User $user): void {
        $currentUpdatedAt = new DateTime($user['updated_at']);
        $requestUpdatedAt = new DateTime($userData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    private static function validateInmutableFieldsDidNotChange(array $userData, User $user): void {
        if ($userData['email'] !== $user->email) {
            throw new InvalidUpdateException();
        }
    }

    public static function deleteUser(User $user): void {
        try {
            $user->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
        }
    }

    public static function findByIdOrFail(int $id): User {
        $user = DB::table('users')->find($id);

        if (!$user)
            throw new NotFoundException();

        $user = User::hydrate([$user])[0];
        $user->emptyPasswordForDataProtection();

        return $user;
    }

    public static function findByEmailAndPasswordOrFail(string $email, string $password): User {
        $user = DB::table('users')
            ->where('email', '=', $email)
            ->where('password', '=', $password)
            ->first();
        
        if (!$user)
            throw new NotFoundException();

        $user = User::hydrate([$user])[0];
        $user->emptyPasswordForDataProtection();

        return $user;
    }
}
