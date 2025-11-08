<?php

namespace Obelaw\Obi\Console\Commands;

use Illuminate\Console\Command;
use Obelaw\Obi\Facades\Obi;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;

class PromptCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obi:prompt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a prompt to Gemini';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $promptInput = text(
            label: 'What is your prompt?',
            placeholder: 'E.g. How to create a new model?',
            required: true,
        );

        info('Sending prompt to Obi...');

        $promptOutput = Obi::prompt($promptInput);

        note($promptOutput);

        return Command::SUCCESS;
    }
}
