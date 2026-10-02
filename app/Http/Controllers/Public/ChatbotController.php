<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ChatbotMessageRequest;
use App\Services\PublicChatbotService;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    public function __construct(private readonly PublicChatbotService $chatbot) {}

    /**
     * Opening message used when the chat panel mounts or is reset.
     */
    public function opening(): JsonResponse
    {
        return response()->json($this->chatbot->opening());
    }

    /**
     * Answer a visitor question from live CMS data.
     *
     * Validation failures are returned as HTTP 422 with the first message so the
     * widget can surface it instead of showing a generic network error.
     */
    public function reply(ChatbotMessageRequest $request): JsonResponse
    {
        return response()->json($this->chatbot->answer($request->question()));
    }
}
