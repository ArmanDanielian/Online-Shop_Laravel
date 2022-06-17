<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $perPage = request()->get('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        return response()->json([
            User::paginate($perPage)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'data' => User::findOrFail($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $validatedData = $request->validated();
        if (isset($validatedData['new_password'])) {
            $validatedData['password'] = bcrypt($validatedData['new_password']);
            unset(
                $validatedData['new_password'],
                $validatedData['new_password_confirmation'],
                $validatedData['current_password']
            );
        }
        $user->update($validatedData);

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return response()->json([
            'message' => 'deleted'
        ], 204);
    }
}
