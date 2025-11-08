<?php

namespace Obelaw\Obi\Console\Commands;

use Illuminate\Console\Command;
use Obelaw\Obi\Declaration;
use Obelaw\Obi\DeclarationPool;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'obi:list 
                            {--enabled : Show only enabled declarations}
                            {--tag= : Filter by tag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available Gemini declarations from pools';

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

        $this->info('Configured declaration pools:');
        foreach ($paths as $path) {
            $this->line("  • {$path}");
        }
        $this->newLine();

        $declarations = $this->collectDeclarations($paths);

        if (empty($declarations)) {
            $this->warn('No declarations found in the configured paths.');
            return Command::SUCCESS;
        }

        // Display declarations table
        $this->displayDeclarationsTable($declarations);

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

                    // Get the function declaration
                    $functionDeclaration = $declarationInstance->declaration();

                    $declarations[] = [
                        'name' => $declarationInstance->getName(),
                        'file' => basename($file),
                        'path' => $path,
                        'description' => $declarationInstance->getDescription(),
                        'function_name' => $functionDeclaration->name,
                        'function_description' => $functionDeclaration->description,
                    ];
                } catch (\Throwable $e) {
                    $this->error("Failed to load declaration from file: {$file}. Error: " . $e->getMessage());
                }
            }
        }

        return $declarations;
    }

    /**
     * Display declarations in a table format.
     *
     * @param array $declarations
     */
    private function displayDeclarationsTable(array $declarations): void
    {
        $headers = ['Name', 'Function', 'File', 'Pool', 'Description'];

        $rows = [];

        foreach ($declarations as $item) {
            // Simplify pool path
            $pool = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $item['path']);
            if (strlen($pool) > 30) {
                $pool = '...' . substr($pool, -27);
            }

            // Truncate description if too long
            $description = $item['description'];
            if (strlen($description) > 50) {
                $description = substr($description, 0, 47) . '...';
            }

            $rows[] = [
                $item['name'],
                $item['function_name'],
                $item['file'],
                $pool,
                $description,
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
        $this->info('Total declarations: ' . count($declarations));
    }
}
