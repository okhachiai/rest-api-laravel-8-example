<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'posts' => Post::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PostStoreRequest $request
     * @return JsonResponse
     */
    public function store(PostStoreRequest $request): JsonResponse
    {
        try {
            $imageName = Str::random(32).".".$request->image->getClientOriginalExtension();

            Post::create([
                'name' => $request->name,
                'image' => $imageName,
                'description' => $request->description
            ]);

            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            return response()->json([
                'message' => "Post successfully created."
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Something went really wrong!"
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function show(Post $post): JsonResponse
    {
        return response()->json([
            'post' => $post
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PostStoreRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(PostStoreRequest $request, Post $post): JsonResponse
    {
        try {
            $post->name = $request->name;
            $post->description = $request->description;

            if ($request->image) {
                $storage = Storage::disk('public');
                if ($storage->exists($post->image)) {
                    $storage->delete($post->image);
                }
                $imageName = Str::random(32).".".$request->image->getClientOriginalExtension();
                $post->image = $imageName;
                $storage->put($imageName, file_get_contents($request->image));
            }
            $post->save();

            return response()->json([
                'message' => "Post successfully updated."
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => "Something went really wrong!"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Post $post): JsonResponse
    {
        $storage = Storage::disk('public');
        if($storage->exists($post->image))
            $storage->delete($post->image);
        $post->delete();

        return response()->json([
            'message' => "Post successfully deleted."
        ], 200);
    }
}
