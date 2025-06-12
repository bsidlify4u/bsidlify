<?php

namespace App\Http\Controllers;

use App\Facades\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class TemplateExampleController extends Controller
{
    /**
     * Show an example using the default template engine.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'title' => 'Template Engine Examples',
            'items' => ['PHP', 'HTML', 'CSS', 'JavaScript'],
            'asset' => 'css/app.css',
        ];
        
        // This will use the default engine from config
        return view('welcome', $data);
    }
    
    /**
     * Show an example using Blade.
     *
     * @return \Illuminate\Http\Response
     */
    public function blade()
    {
        $data = [
            'title' => 'Blade Template Example',
            'items' => ['PHP', 'HTML', 'CSS', 'JavaScript'],
            'asset' => 'css/app.css',
        ];
        
        // Use the blade example template
        return view('examples.blade-example', $data);
    }
    
    /**
     * Show an example using Twig.
     *
     * @return \Illuminate\Http\Response
     */
    public function twig()
    {
        $data = [
            'title' => 'Twig Template Example',
            'items' => ['PHP', 'HTML', 'CSS', 'JavaScript'],
            'asset' => 'css/app.css',
        ];
        
        // Use our template facade with a specific driver
        $content = Template::driver('twig')->render('examples.twig-example', $data);
        return response($content);
    }
    
    /**
     * Show an example using Plates.
     *
     * @return \Illuminate\Http\Response
     */
    public function plates()
    {
        $data = [
            'title' => 'Plates Template Example',
            'items' => ['PHP', 'HTML', 'CSS', 'JavaScript'],
            'asset' => 'css/app.css',
        ];
        
        // Use our template facade with a specific driver
        $content = Template::driver('plates')->render('examples.plate-example', $data);
        return response($content);
    }
    
    /**
     * Show an example auto-detecting the template engine.
     *
     * @param  string  $view
     * @return \Illuminate\Http\Response
     */
    public function autoDetect($view)
    {
        $data = [
            'title' => 'Auto-Detected Template Example',
            'items' => ['PHP', 'HTML', 'CSS', 'JavaScript'],
            'asset' => 'css/app.css',
        ];
        
        // This will auto-detect the engine based on the view extension
        $content = Template::render('examples.' . $view, $data);
        return response($content);
    }
} 