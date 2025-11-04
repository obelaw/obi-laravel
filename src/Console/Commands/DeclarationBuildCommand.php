<?php

namespace Obelaw\Obi\Console\Commands;

use Illuminate\Console\Command;
use Obelaw\Obi\Declaration;
use Obelaw\Obi\DeclarationPool;
use Obelaw\Obi\Models\ObiDeclaration;

class DeclarationBuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'declaration:build 
                            {--fresh : Clear all existing declarations before building}
                            {--tag= : Build only declarations with specific tag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build and store all Gemini declarations into the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $paths = DeclarationPool::getPaths();

        if (empty($paths)) {
            $this->error('No declaration paths configured.');
            return Command::FAILURE;
        }

        // Clear existing declarations if --fresh flag is set
        if ($this->option('fresh')) {
            $this->info('Clearing existing declarations...');
            ObiDeclaration::truncate();
        }

        $this->info('Building declarations from pools:');
        foreach ($paths as $path) {
            $this->line("  • {$path}");
        }
        $this->newLine();

        $declarations = $this->collectDeclarations($paths);

        if (empty($declarations)) {
            $this->warn('No declarations found in the configured paths.');
            return Command::SUCCESS;
        }

        $this->info('Processing declarations...');
        $progressBar = $this->output->createProgressBar(count($declarations));
        $progressBar->start();

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($declarations as $item) {
            try {
                $this->storeDeclaration($item, $created, $updated);
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("Failed to store declaration from {$item['file']}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('Build Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Failed', $failed],
                ['Total', count($declarations)],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Collect all declarations from the specified paths.
     *
     * @param array $paths
     * @return array
     */
    private function collectDeclarations(array $paths): array
    {
        $declarations = [];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = glob($path . '/*.php');
            if ($files === false) {
                continue;
            }

            foreach ($files as $file) {
                try {
                    // Load the declaration file (returns anonymous class instance)
                    $declarationInstance = require $file;

                    // Check if it's a valid Declaration instance
                    if (!$declarationInstance instanceof Declaration) {
                        continue;
                    }

                    // Filter by tag if specified
                    $tag = $this->option('tag');
                    if ($tag && method_exists($declarationInstance, 'getTag')) {
                        if ($declarationInstance->getTag() !== $tag) {
                            continue;
                        }
                    }

                    // Get the function declaration
                    $functionDeclaration = $declarationInstance->declaration();

                    $declarations[] = [
                        'file' => basename($file),
                        'function_name' => $functionDeclaration->name,
                        'function_description' => $functionDeclaration->description,
                        'declaration' => serialize($functionDeclaration),
                        'target_class' => $declarationInstance->targetClass(),
                        'target_method' => $declarationInstance->targetMethod(),
                        'tag' => $declarationInstance->getTag(),
                    ];
                } catch (\Throwable $e) {
                    // Skip invalid files
                    continue;
                }
            }
        }

        return $declarations;
    }

    /**
     * Store or update a declaration in the database.
     *
     * @param array $item
     * @param int &$created
     * @param int &$updated
     * @return void
     */
    private function storeDeclaration(array $item, int &$created, int &$updated): void
    {
        // Check if declaration already exists by file or function_name
        $existing = ObiDeclaration::where('file', $item['file'])
            ->orWhere('function_name', $item['function_name'])
            ->first();

        $data = [
            'file' => $item['file'],
            'function_name' => $item['function_name'],
            'function_description' => $item['function_description'],
            'declaration' => $item['declaration'],
            'target_class' => $item['target_class'],
            'target_method' => $item['target_method'],
            'tag' => $item['tag'],
            'enabled' => true,
        ];

        if ($existing) {
            $existing->update($data);
            $updated++;
        } else {
            ObiDeclaration::create($data);
            $created++;
        }
    }
}
