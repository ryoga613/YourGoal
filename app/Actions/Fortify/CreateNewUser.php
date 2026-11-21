<?php

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * 一般の新規登録（Fortify）は role を渡さず一般ユーザーになる。管理者の新規登録だけ UserRole::Admin を渡す
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input, UserRole $role = UserRole::User): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user = new User([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        // role は Fillable に含めていないため、ここで直接設定する
        $user->role = $role;
        $user->save();

        return $user;
    }
}
