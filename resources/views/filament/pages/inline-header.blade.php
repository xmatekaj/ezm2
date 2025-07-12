<!DOCTYPE html>
<div class="flex items-center justify-between py-4 px-6 bg-white border-b border-gray-200">
    <div class="flex-1">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $title }}
        </h1>
    </div>
    
    <div class="flex items-center space-x-2">
        @foreach($actions as $action)
            {{ $action }}
        @endforeach
    </div>
</div>