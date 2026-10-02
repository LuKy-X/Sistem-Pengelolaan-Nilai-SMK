<?php

namespace App\Services;

use App\Ai\Agents\SchoolAssistant;
use App\Ai\Participants\GuestChatbotParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

/**
 * Orchestrates the AI chatbot for the public school website.
 *
 * Answers known school questions from the local CMS before using Gemini for
 * questions that do not match a local topic.
 */
class AiChatbotService
{
    /**
     * Static suggestions shown after an AI response when the agent
     * does not produce its own follow-up prompts.
     *
     * @var list<string>
     */
    private const DEFAULT_SUGGESTIONS = [
        'Jurusan apa saja?',
        'Berapa jumlah siswa per jurusan?',
        'Siapa guru pengampu di tiap kelas?',
    ];

    public function __construct(private readonly PublicChatbotService $publicChatbot) {}

    /**
     * Answer locally when a school topic matches, otherwise use the AI agent.
     *
     * @return array{intent: string, reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>, conversation_id: string|null}
     */
    public function answer(string $question, ?string $conversationId = null): array
    {
        $localAnswer = $this->publicChatbot->answer($question);

        if ($localAnswer['intent'] !== 'fallback') {
            return [...$localAnswer, 'conversation_id' => null];
        }

        try {
            $agent = SchoolAssistant::make();

            // Tie the anonymous conversation to the visitor's browser session.
            $participant = $this->guestParticipant();

            if ($conversationId) {
                $agent->continue($conversationId, as: $participant);
            } else {
                $agent->forParticipant($participant);
            }

            $response = $agent->prompt($question);

            $newConversationId = $response->conversationId ?? $conversationId;

            Log::info('AI chatbot response', [
                'conversation_id' => $newConversationId,
                'tools_used' => collect($response->steps)->flatMap(fn ($step) => collect($step->toolResults)->pluck('toolName'))->values()->all(),
            ]);

            return [
                'intent' => 'ai',
                'reply' => $response->text,
                'suggestions' => self::DEFAULT_SUGGESTIONS,
                'links' => [],
                'conversation_id' => $newConversationId,
            ];
        } catch (RateLimitedException $e) {
            Log::warning('AI chatbot rate limited', ['error' => $e->getMessage()]);

            return $this->errorResponse('Chatbot sedang sibuk. Silakan coba lagi dalam beberapa saat.');
        } catch (InsufficientCreditsException $e) {
            Log::error('AI chatbot insufficient credits', ['error' => $e->getMessage()]);

            return $this->errorResponse('Layanan AI sedang tidak tersedia. Silakan hubungi administrator.');
        } catch (ProviderConnectionException $e) {
            Log::error('AI chatbot connection error', ['error' => $e->getMessage()]);

            return $this->errorResponse('Tidak dapat terhubung ke layanan AI. Periksa koneksi internet.');
        } catch (Throwable $e) {
            Log::error('AI chatbot unexpected error', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return $this->errorResponse('Maaf, chatbot sedang mengalami gangguan. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Opening message shown when the chat panel first opens.
     *
     * @return array{reply: string, suggestions: list<string>}
     */
    public function opening(): array
    {
        return $this->publicChatbot->opening();
    }

    /**
     * Build an error response in the chatbot shape.
     *
     * @return array{reply: string, suggestions: list<string>, links: list<array{label: string, url: string}>, conversation_id: null}
     */
    private function errorResponse(string $message): array
    {
        return [
            'intent' => 'ai',
            'reply' => $message,
            'suggestions' => self::DEFAULT_SUGGESTIONS,
            'links' => [],
            'conversation_id' => null,
        ];
    }

    /**
     * Create a lightweight guest participant object for conversation storage.
     *
     * Uses session ID so conversations survive page reloads without login.
     */
    private function guestParticipant(): GuestChatbotParticipant
    {
        $sessionId = session()->getId() ?? Str::uuid()->toString();
        $participantId = hexdec(substr(hash('sha256', $sessionId), 0, 15));

        return new GuestChatbotParticipant($participantId);
    }
}
