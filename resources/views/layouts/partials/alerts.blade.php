@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800" x-data="{ show: true }" x-show="show">
        <div class="flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800" x-data="{ show: true }" x-show="show">
        <div class="flex justify-between items-center">
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-800">&times;</button>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-red-800">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
