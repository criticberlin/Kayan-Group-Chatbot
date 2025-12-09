<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Product;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        $message = $validated['message'];
        $sessionId = $validated['session_id'] ?? Str::uuid()->toString();
        $startTime = microtime(true);

        try {
            // Send RELEVANT products to Gemini (optimized: top 50 most relevant)
            // First try to find relevant ones, then fill with random if needed
            $relevantProducts = Product::where(function ($q) use ($message) {
                $searchTerms = explode(' ', strtolower($message));
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $q->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('category', 'like', "%{$term}%");
                    }
                }
            })->take(30)->get();

            // Fill remaining with random products
            $remaining = max(0, 50 - $relevantProducts->count());
            $randomProducts = Product::whereNotIn('id', $relevantProducts->pluck('id'))
                ->inRandomOrder()
                ->take($remaining)
                ->get();

            $allProducts = $relevantProducts->merge($randomProducts)->map(function ($product) {
                return [
                    'name' => $product->name,
                    'category' => $product->category,
                    'price' => ($product->price > 0) ? "{$product->currency} " . number_format((float) $product->price, 2) : "Contact for price",
                ];
            })->toArray();

            // Get conversation history
            $history = $this->chatService->getConversationHistory($sessionId, 3);

            // Debug logging
            \Log::info('Sending to n8n:', [
                'message' => $message,
                'total_products' => count($allProducts),
                'webhook_url' => config('services.n8n.chat_webhook_url')
            ]);

            // Send to n8n webhook
            $n8nResponse = Http::timeout(60)->post(config('services.n8n.chat_webhook_url'), [
                'message' => $message,
                'context' => $allProducts,  // Send optimized product list
                'history' => $history,
                'session_id' => $sessionId,
            ]);

            if (!$n8nResponse->successful()) {
                throw new \Exception('n8n webhook failed: ' . $n8nResponse->body());
            }

            $responseData = $n8nResponse->json();
            $aiResponse = $responseData['response'] ?? 'Sorry, I could not generate a response.';
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            // Save conversation
            $conversation = ChatConversation::create([
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'message' => $message,
                'response' => $aiResponse,
                'context_used' => ['total_products' => count($allProducts)], // Don't store all 150, just count
                'model' => $responseData['model'] ?? 'gemini-pro',
                'response_time_ms' => $responseTime,
                'was_successful' => true,
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'message' => $message,
                'response' => $aiResponse,
                'response_time_ms' => $responseTime,
            ]);

        } catch (\Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            // Log failed attempt
            ChatConversation::create([
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'message' => $message,
                'response' => null,
                'context_used' => $allProducts ?? [],
                'model' => 'gemini-pro',
                'response_time_ms' => $responseTime,
                'was_successful' => false,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get AI response. Please try again.',
                'session_id' => $sessionId,
            ], 500);
        }
    }

    public function getHistory(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return response()->json(['error' => 'session_id required'], 400);
        }

        $conversations = ChatConversation::bySession($sessionId)
            ->successful()
            ->get();

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'conversations' => $conversations,
        ]);
    }
}
