<?php

namespace App\Http\Requests\Public;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a single visitor message sent to the public AI chatbot endpoint.
 */
class ChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:500'],
            'conversation_id' => ['nullable', 'string', 'max:36'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Pertanyaanmu belum diisi.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan maksimal 500 karakter.',
        ];
    }

    /**
     * Normalised visitor message, ready for the AI agent.
     */
    public function question(): string
    {
        return trim((string) $this->input('message'));
    }

    /**
     * Optional conversation ID to continue an existing conversation.
     */
    public function conversationId(): ?string
    {
        $value = $this->input('conversation_id');

        return filled($value) ? (string) $value : null;
    }
}
