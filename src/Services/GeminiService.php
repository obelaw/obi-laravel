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

        $client = Gemini::client(env('GEMINI_API_KEY'));

        // 2. Define your function(s) for the model
        $findMeetingTimeTool = new Tool(
            functionDeclarations: $declarationService->getAll()
        );

        // 3. Start a chat session with the tool
        $chat = $client->generativeModel('gemini-2.5-flash')
            ->withTool($findMeetingTimeTool)
            ->startChat();

        // 4. Send the user's prompt
        $response = $chat->sendMessage($userPrompt);

        // 5. Check if the model responded with a function call
        $part = $response->parts()[0];

        if ($part->functionCall !== null) {
            $functionCall = $part->functionCall;
            // echo "Model wants to call function: {$functionCall->name}\n";

            // 6. Execute your local PHP function
            // var_dump($functionCall->args, $functionCall->name);

            $functionResult = $declarationService->execute($functionCall->name, $functionCall->args);

            // *** 7. (THIS WAS THE MISSING STEP) Send the function's result back to the model ***
            $response = $chat->sendMessage(
                new Content(
                    parts: [
                        new Part(
                            functionResponse: new FunctionResponse(
                                name: $functionCall->name,
                                response: $functionResult // Pass the array result directly
                            )
                        )
                    ],
                    role: Role::USER // This role is crucial
                )
            );
        }

        return $response->text();
    }
}
