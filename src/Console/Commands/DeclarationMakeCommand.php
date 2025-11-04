<?php

namespace Obelaw\Obi\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Obelaw\Obi\DeclarationPool;

class DeclarationMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'declaration:make {name : The name of the declaration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Gemini declaration file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        
        // Get available pools
        $pools = DeclarationPool::getPaths();
        
        if (empty($pools)) {
            $this->error('No declaration pools configured.');
            return Command::FAILURE;
        }

        // Let user select a pool
        $selectedPool = $this->choice(
            'Select a declaration pool',
            $pools,
            0
        );

        // Generate filename with timestamp
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . Str::snake($name) . '.php';
        
        // Create directory if it doesn't exist
        if (!is_dir($selectedPool)) {
            mkdir($selectedPool, 0755, true);
            $this->info("Created pool directory: {$selectedPool}");
        }

        $filePath = $selectedPool . DIRECTORY_SEPARATOR . $fileName;

        // Check if file already exists
        if (file_exists($filePath)) {
            $this->error("Declaration file already exists: {$fileName}");
            return Command::FAILURE;
        }

        // Generate the file content
        $content = $this->generateDeclarationContent(Str::camel($name));

        // Write the file
        file_put_contents($filePath, $content);

        $this->info("Declaration created successfully!");
        $this->newLine();
        $this->line("File: {$fileName}");
        $this->line("Pool: {$selectedPool}");
        $this->line("Path: {$filePath}");
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('1. Edit the file to customize the function parameters and description');
        $this->line('2. Set the target class and method');
        $this->line('3. Run: php artisan declaration:build');

        return Command::SUCCESS;
    }

    /**
     * Generate the declaration file content.
     *
     * @param string $functionName
     * @return string
     */
    private function generateDeclarationContent(string $functionName): string
    {
        return <<<'PHP'
<?php

use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Obelaw\Obi\Declaration;

return new class extends Declaration
{
    public ?string $tag = null;

    public function declaration(): FunctionDeclaration
    {
        return new FunctionDeclaration(
            name: 'functionName',
            description: 'Description of what this function does.',
            parameters: new Schema(
                type: DataType::OBJECT,
                properties: [
                    // Add your parameters here
                    // Example:
                    // 'status' => new Schema(
                    //     type: DataType::STRING,
                    //     description: 'The status filter.'
                    // ),
                ],
                required: [] // Add required parameter names here
            )
        );
    }

    public function targetClass(): string
    {
        return ''; // Example: \App\Services\YourService::class
    }

    public function targetMethod(): string
    {
        return ''; // Example: 'yourMethod'
    }
};

PHP;
    }
}