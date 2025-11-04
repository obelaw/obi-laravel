<?php

namespace Obelaw\Obi\Services;

use Gemini;
use Gemini\Data\Content;
use Gemini\Data\FunctionResponse;
use Gemini\Data\Part;
use Gemini\Data\Tool;
use Gemini\Enums\Role;
use Obelaw\Obi\Services\DeclarationService;

class GeminiService
{
    public function prompt(string $userPrompt)
    {
        $declarationService = new DeclarationService;

        $apiKey = config('obi.api_key');
        $model = config('obi.model', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            throw new \Exception('GEMINI_API_KEY is not configured. Please set it in your .env file or config/obi.php');
        }

        $client = Gemini::client($apiKey);

        // 2. Define your function(s) for the model
        $findMeetingTimeTool = new Tool(
            functionDeclarations: $declarationService->getAll()
        );

        // 3. Start a chat session with the tool
        $chat = $client->generativeModel($model)
            ->withTool($findMeetingTimeTool)
            ->startChat();

        // 4. Send the user's prompt
        $response = $chat->sendMessage($userPrompt);

        // 5. Check if the model responded with a function call
        $part = $response->parts()[0];

        if ($part->functionCall !== null) {
            $functionCall = $part->functionCall;
            
            // Log if enabled
            if (config('obi.logging.enabled')) {
                \Log::channel(config('obi.logging.channel'))
                    ->info('Gemini function call', [
                        'function' => $functionCall->name,
                        'args' => $functionCall->args,
                    ]);
            }

            // 6. Execute your local PHP function
            $functionResult = $declarationService->execute($functionCall->name, $functionCall->args);

            // *** 7. Send the function's result back to the model ***
            $response = $chat->sendMessage(
                new Content(
                    parts: [
                        new Part(
                            functionResponse: new FunctionResponse(
                                name: $functionCall->name,
                                response: $functionResult
                            )
                        )
                    ],
                    role: Role::USER
                )
            );
        }

        return $response->text();
    }
}
