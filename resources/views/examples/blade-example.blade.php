<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Blade Example - {{ $title }}</title>
        
        @if(app()->environment('local'))
            <script src="http://localhost:3000/{{ $asset }}"></script>
        @else
            <link rel="stylesheet" href="{{ $asset }}">
        @endif
    </head>
    <body>
        <div class="container">
            <h1>{{ $title }}</h1>
            
            <p>This is an example Blade template in Bsidlify.</p>
            
            @if(isset($items) && count($items) > 0)
                <ul>
                @foreach($items as $item)
                    <li>{{ $item }}</li>
                @endforeach
                </ul>
            @else
                <p>No items to display.</p>
            @endif
            
            <p>Current time: {{ date('Y-m-d H:i:s') }}</p>
            
            @include('examples.partials.footer-blade')
        </div>
    </body>
</html> 