<?php

namespace Cartxis\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid email address.',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');

        try {
            // Check if email already exists in customers table
            $customer = DB::table('customers')
                ->where('email', $email)
                ->first();

            if ($customer) {
                // Update existing customer
                if ($customer->newsletter_subscribed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already subscribed to our newsletter.'
                    ], 409);
                }

                DB::table('customers')
                    ->where('email', $email)
                    ->update([
                        'newsletter_subscribed' => true,
                        'updated_at' => now()
                    ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully subscribed! Welcome back to our newsletter.'
                ]);
            }

            // Create a new newsletter-only customer record
            DB::table('customers')->insert([
                'first_name' => 'Newsletter',
                'last_name' => 'Subscriber',
                'email' => $email,
                'is_active' => true,
                'is_verified' => false,
                'newsletter_subscribed' => true,
                'customer_group_id' => 1, // Default customer group
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed! Check your inbox for confirmation.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Newsletter subscription error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid email address.',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');

        $updated = DB::table('customers')
            ->where('email', $email)
            ->where('newsletter_subscribed', true)
            ->update([
                'newsletter_subscribed' => false,
                'updated_at' => now()
            ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from newsletter.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email not found in our newsletter list.'
        ], 404);
    }
}
