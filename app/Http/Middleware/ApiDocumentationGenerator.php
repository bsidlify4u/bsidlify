<?php

namespace App\Http\Middleware;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Routing\Route;

class ApiDocumentationGenerator
{
    protected $docs = [];
    protected $excludedMethods = ['__construct', '__invoke'];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->query('docs', false)) {
            return $this->generateDocs($request->route());
        }

        return $response;
    }

    protected function generateDocs(Route $route)
    {
        $controller = $route->getController();
        $reflection = new ReflectionClass($controller);
        
        $this->docs = [
            'endpoint' => $route->uri(),
            'method' => implode('|', $route->methods()),
            'controller' => $reflection->getName(),
            'description' => $this->getClassDocComment($reflection),
            'parameters' => $this->getMethodParameters($reflection, $route->getActionMethod()),
            'responses' => $this->getMethodResponses($reflection, $route->getActionMethod()),
            'examples' => $this->getMethodExamples($reflection, $route->getActionMethod()),
        ];

        return response()->json($this->docs);
    }

    protected function getClassDocComment(ReflectionClass $reflection): string
    {
        $docComment = $reflection->getDocComment();
        return $this->parseDocComment($docComment);
    }

    protected function getMethodParameters(ReflectionClass $reflection, string $method): array
    {
        $methodReflection = $reflection->getMethod($method);
        $params = [];

        foreach ($methodReflection->getParameters() as $param) {
            $params[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                'required' => !$param->isOptional(),
                'default' => $param->isOptional() ? $param->getDefaultValue() : null,
            ];
        }

        return $params;
    }

    protected function getMethodResponses(ReflectionClass $reflection, string $method): array
    {
        $methodReflection = $reflection->getMethod($method);
        $docComment = $methodReflection->getDocComment();
        
        preg_match_all('/@response\s+(\d+)\s+(.+)/m', $docComment, $matches, PREG_SET_ORDER);
        
        $responses = [];
        foreach ($matches as $match) {
            $responses[$match[1]] = json_decode($match[2], true);
        }

        return $responses;
    }

    protected function getMethodExamples(ReflectionClass $reflection, string $method): array
    {
        $methodReflection = $reflection->getMethod($method);
        $docComment = $methodReflection->getDocComment();
        
        preg_match_all('/@example\s+(.+)/m', $docComment, $matches, PREG_SET_ORDER);
        
        return array_map(function($match) {
            return $match[1];
        }, $matches);
    }

    protected function parseDocComment(?string $docComment): string
    {
        if (!$docComment) {
            return '';
        }

        // Remove comment markers and asterisks
        $docComment = preg_replace('/^\s*\/\*+\s*|^\s*\*+\/\s*|\s*\*\s/m', '', $docComment);
        
        // Remove @ annotations
        $docComment = preg_replace('/@.+/m', '', $docComment);
        
        return trim($docComment);
    }
}
