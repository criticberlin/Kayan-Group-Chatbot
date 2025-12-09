<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ChatService
{
    /**
     * Generate relevant product context based on user message
     */
    public function generateProductContext(string $message, int $limit = 10): array
    {
        // First, try searching for the full message as a phrase (after common words)
        $cleanMessage = $this->removeCommonWords($message);
        
        if (strlen($cleanMessage) > 3) {
            $phraseResults = Product::where('name', 'like', "%{$cleanMessage}%")
                ->orWhere('name_arabic', 'like', "%{$cleanMessage}%")
                ->take($limit)
                ->get();
            
            if ($phraseResults->isNotEmpty()) {
                return $this->formatProductsForContext($phraseResults);
            }
        }

        // If phrase search didn't work, fall back to keyword extraction
        $keywords = $this->extractKeywords($message);

        if (empty($keywords)) {
            // Return random featured products if no keywords
            return $this->getFeaturedProducts($limit);
        }

        $products = $this->searchProducts($keywords, $limit);

        return $this->formatProductsForContext($products);
    }
    
    /**
     * Remove common words from message for phrase matching
     */
    private function removeCommonWords(string $message): string
    {
        $message = strtolower($message);
        $commonWords = ['how', 'much', 'is', 'the', 'what', 'tell', 'me', 'about', 'show'];
        
        $words = explode(' ', $message);
        $filtered = array_filter($words, function($word) use ($commonWords) {
            return !in_array($word, $commonWords) && strlen($word) > 2;
        });
        
        return trim(implode(' ', $filtered));
    }

    /**
     * Extract keywords from user message
     */
    private function extractKeywords(string $message): array
    {
        $message = strtolower($message);

        // Remove common words
        $commonWords = [
            'the',
            'a',
            'an',
            'and',
            'or',
            'but',
            'in',
            'on',
            'at',
            'to',
            'for',
            'tell',
            'me',
            'about',
            'what',
            'which',
            'how',
            'do',
            'does',
            'is',
            'are',
            'you',
            'have',
            'your',
            'can',
            'i',
            'get',
            'want',
            'need',
            'looking',
            'much',
            'size', // Removed: interferes with "Family Size"
            'big',  // Removed: interferes with product names
            'small',
            'tiny',
            'mini',
            'family'
        ];

        $words = preg_split('/\s+/', $message);
        $keywords = array_filter($words, function ($word) use ($commonWords) {
            return strlen($word) > 2 && !in_array($word, $commonWords);
        });

        return array_values($keywords);
    }

    /**
     * Search for products matching keywords
     */
    private function searchProducts(array $keywords, int $limit): Collection
    {
        // First, try to find products with ALL keywords (more specific)
        $query = Product::query();

        // Build a search that requires ALL keywords to match
        $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->where(function ($subQ) use ($keyword) {
                    $subQ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('name_arabic', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('category_arabic', 'like', "%{$keyword}%")
                        ->orWhere('type_english', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            }
        });

        $results = $query->take($limit)->get();

        // If we don't have enough results, do a broader search with OR logic
        if ($results->count() < $limit) {
            $remaining = $limit - $results->count();
            $resultIds = $results->pluck('id')->toArray();

            $broadQuery = Product::query()->whereNotIn('id', $resultIds);
            $broadQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('name_arabic', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('category_arabic', 'like', "%{$keyword}%")
                        ->orWhere('type_english', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                }
            });

            $additionalResults = $broadQuery->take($remaining)->get();
            $results = $results->merge($additionalResults);
        }

        return $results;
    }

    /**
     * Get featured/random products
     */
    private function getFeaturedProducts(int $limit): array
    {
        $products = Product::inRandomOrder()
            ->take($limit)
            ->get();

        return $this->formatProductsForContext($products);
    }

    /**
     * Format products for AI context
     */
    private function formatProductsForContext(Collection $products): array
    {
        return $products->map(function ($product) {
            return [
                'name' => $product->name,
                'name_arabic' => $product->name_arabic,
                'category' => $product->category,
                'type' => $product->type_english,
                'price' => "{$product->currency} " . number_format($product->price, 2),
                'description' => $product->description ?? $product->description_arabic,
                'manufacturer' => $product->manufacturer,
            ];
        })->toArray();
    }

    /**
     * Get conversation history for a session
     */
    public function getConversationHistory(string $sessionId, int $limit = 5): array
    {
        $conversations = \App\Models\ChatConversation::bySession($sessionId)
            ->successful()
            ->latest()
            ->take($limit)
            ->get()
            ->reverse();

        return $conversations->map(function ($conv) {
            return [
                'message' => $conv->message,
                'response' => $conv->response,
            ];
        })->toArray();
    }
}
