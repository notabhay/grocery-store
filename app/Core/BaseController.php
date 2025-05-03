<?php

namespace App\Core;

use App\Core\Registry; 


abstract class BaseController
{
    
    protected function view(string $view, array $data = []): void
    {
        
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        $layoutPath = __DIR__ . '/../Views/layouts/default.php';

        
        if (!file_exists($viewPath)) {
            trigger_error("View file not found: {$viewPath}", E_USER_WARNING);
            echo "Error: View file '{$view}' not found.";
            exit; 
        }

        
        if (!file_exists($layoutPath)) {
            trigger_error("Layout file not found: {$layoutPath}", E_USER_WARNING);
            echo "Error: Default layout file not found.";
            exit; 
        }

        
        try {
            $request = Registry::get('request'); 
            $uri = $request->uri(); 
            $currentPath = '/' . ($uri ?: ''); 
            $data['currentPath'] = $currentPath; 
        } catch (\Exception $e) {
            
            $data['currentPath'] = '/';
        }

        
        
        extract($data);

        
        ob_start();

        try {
            
            include $viewPath;
        } catch (\Throwable $e) {
            
            ob_end_clean(); 
            
            echo "Error rendering view '{$view}'. Please check the logs.";
            exit; 
        }

        
        $content = ob_get_clean();

        
        $data['content'] = $content;

        
        
        extract($data);

        
        include $layoutPath;
    }
}
