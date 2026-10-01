<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ChatbotMessageRequest;
use App\Services\AuthenticatedChatbotService;
use Illuminate\Http\JsonResponse;

/**
 * Chatbot endpoint for authenticated users (teacher, student, counselor).
 *
 * All answers are scoped to the authenticated user's own data, so a student
 * can never see another student's grades, and a teacher only sees their own
 * gradebooks and assessments.
 */
class AuthenticatedChatbotController extends Controller
{
    public function __construct(private readonly AuthenticatedChatbotService $chatbot) {}

    /**
     * Opening message personalised to the authenticated user's role.
     */
    public function opening(): JsonResponse
    {
        return response()->json($this->chatbot->opening(auth()->user()));
    }

    /**
     * Answer an authenticated user question from their private data only.
     */
    public function reply(ChatbotMessageRequest $request): JsonResponse
    {
        return response()->json($this->chatbot->answer(auth()->user(), $request->question()));
    }
}
