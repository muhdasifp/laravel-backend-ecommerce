<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RatingController extends Controller
{
    public function addRating(Request $request)
    {
        $user = JWTAuth::user();

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|numeric'
        ]);


        $existingRating = Rating::where('user_id', $user->id)->where('product_id', $request->product_id)->first();

        if ($existingRating) {
            $existingRating->update(['rating' => $request->rating]);
            return response()->json($existingRating, 200);
        }

        $rating = new Rating();
        $rating->user_id = $user->id;
        $rating->product_id = $request->product_id;
        $rating->rating = $request->rating;
        $rating->save();

        return response()->json(['message' => 'Rating Added successfully'], 201);
    }

    public function destroyRating($id)
    {
        $user = JWTAuth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rating = Rating::where('user_id', $user->id)->find($id);

        if (!$rating) {
            return response()->json(['error' => 'Rating not found'], 404);
        }
        $rating->delete();
        return response()->json(['message' => 'Rating deleted successfully']);
    }
}
