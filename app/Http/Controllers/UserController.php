<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

use Exception;

use App\Models\User;
use App\Exceptions\CustomException;
use App\Exceptions\UnexpectedErrorException;

class UserController extends Controller
{
    public function getById(int $id): JsonResponse {
        try {
            return $this->getUserById($id);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getUserById(int $id): JsonResponse {
        $user = User::findByIdOrFail($id);

        return parent::createJsonResponse($user, Response::HTTP_OK);
    }

    public function getByEmailAndPassword(string $email, string $password): JsonResponse {
        try {
            return $this->getUserByEmailAndPassword($email, $password);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function getUserByEmailAndPassword(string $email, string $password): JsonResponse {
        $user = User::findByEmailAndPasswordOrFail($email, $password);

        return parent::createJsonResponse($user, Response::HTTP_OK);
    }

    public function create(Request $request): JsonResponse {
        try {
            return $this->createUser($request);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function createUser(Request $request): JsonResponse {
        $requestData = $request->all();
        $user = User::createUser($requestData);

        return parent::createJsonResponse($user, Response::HTTP_OK);
    }

    public function update(Request $request, User $user): JsonResponse {
        try {
            return $this->updateUser($request, $user);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function updateUser(Request $request, User $user): JsonResponse {
        $requestData = $request->all();
        $user = User::updateUser($requestData, $user);

        return parent::createJsonResponse($user, Response::HTTP_OK);
    }

    public function delete(User $user): JsonResponse {
        try {
            return $this->deleteUser($user);
        } catch (CustomException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UnexpectedErrorException();
        }
    }

    private function deleteUser(User $user): JsonResponse {
        User::deleteUser($user);

        return parent::createJsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
