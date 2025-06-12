<?php

namespace App\Console\Commands\ApiDocs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class GenerateCommand extends Command
{
    protected $signature = 'api:docs 
                          {--format=json : Output format (json, yaml, markdown)} 
                          {--output= : Output file path}';

    protected $description = 'Generate API documentation from route annotations';

    public function handle()
    {
        $format = $this->option('format');
        $output = $this->option('output');

        $routes = Route::getRoutes();
        $docs = app('api.documentation')->generateAll($routes);

        if ($output) {
            file_put_contents($output, $this->formatDocs($docs, $format));
            $this->info("Documentation generated at: {$output}");
        } else {
            $this->output->write($this->formatDocs($docs, $format));
        }

        return Command::SUCCESS;
    }

    protected function formatDocs($docs, $format)
    {
        return match ($format) {
            'json' => json_encode($docs, JSON_PRETTY_PRINT),
            'yaml' => yaml_emit($docs),
            'markdown' => $this->docsToMarkdown($docs),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    protected function docsToMarkdown($docs): string
    {
        $md = "# API Documentation\n\n";
        
        foreach ($docs as $path => $endpoint) {
            $md .= "## {$endpoint['method']} {$path}\n\n";
            $md .= "{$endpoint['description']}\n\n";
            
            if (!empty($endpoint['parameters'])) {
                $md .= "### Parameters\n\n";
                foreach ($endpoint['parameters'] as $param) {
                    $md .= "- `{$param['name']}` ({$param['type']})";
                    $md .= $param['required'] ? " **Required**" : " Optional";
                    $md .= "\n";
                }
                $md .= "\n";
            }
            
            if (!empty($endpoint['responses'])) {
                $md .= "### Responses\n\n";
                foreach ($endpoint['responses'] as $code => $response) {
                    $md .= "#### {$code}\n\n```json\n" . 
                           json_encode($response, JSON_PRETTY_PRINT) . 
                           "\n```\n\n";
                }
            }
        }
        
        return $md;
    }
}
