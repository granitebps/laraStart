<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth:api');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// $this->authorize('isAdmin');
		if (Gate::allows('isAdmin') || Gate::allows('isAuthor')) {
			return User::latest()->paginate(5);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|string|max:191',
			'email' => 'required|email|string|max:191|unique:users',
			'password' => 'required|string|min:8',
			'role' => 'required'
		]);

		return User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => Hash::make($request->password),
			'role' => $request->role,
			'bio' => $request->bio,
			'photo' => $request->photo
		]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	public function profile()
	{
		return auth('api')->user();
	}

	public function updateProfile(Request $request)
	{
		$user =  auth('api')->user();

		$this->validate($request, [
			'name' => 'required|string|max:191',
			'email' => 'required|email|string|max:191|unique:users,email,' . $user->id,
			'password' => 'sometimes|required|min:8',
			'role' => 'required'
		]);

		$currentPhoto = $user->photo;
		if ($request->photo != $currentPhoto) {
			$ext = explode(';', explode('/', $request->photo)[1])[0];
			$name = time() . '.' . $ext;
			\Image::make($request->photo)->save(public_path('img/profile/') . $name);

			$request->merge(['photo' => $name]);

			$userPhoto = public_path('img/profile/') . $currentPhoto;
			if (file_exists($userPhoto)) {
				@unlink($userPhoto);
			}
		}

		if (!empty($request->password)) {
			$request->merge(['password' => Hash::make($request->password)]);
		}

		$user->update($request->all());
		return ['message' => 'Profile Updated'];
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$user = User::findOrFail($id);

		$this->validate($request, [
			'name' => 'required|string|max:191',
			'email' => 'required|email|string|max:191|unique:users,email,' . $id,
			'password' => 'sometimes|min:8',
			'role' => 'required'
		]);

		$user->update($request->all());
		return ['message' => 'User Updated'];
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$this->authorize('isAdmin');

		$user = User::findOrFail($id);
		$user->delete();
		return ['message' => 'User Deleted'];
	}

	public function search()
	{
		if ($search = \Request::get('q')) {
			$user = User::where(function ($query) use ($search) {
				$query->where('name', 'LIKE', "%$search%")
					->orWhere('email', 'LIKE', "%$search%")
					->orWhere('role', 'LIKE', "%$search%");
			})->paginate(5);
		} else {
			$user = User::latest()->paginate(5);
		}
		return $user;
	}
}
