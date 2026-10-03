@foreach($notifications as $n)
    <div class="p-3 border-b {{ $n->read_at ? 'bg-white' : 'bg-blue-50' }}">
        <span class="{{ $n->data['type'] === 'leave' ? 'text-orange-500' : 'text-blue-500' }}">
            {{ $n->data['type'] === 'leave' ? '🏖️ Leave' : '📋 Task' }}
        </span>
        <p>{{ $n->data['message'] }}</p>
        <small class="text-gray-400">{{ $n->created_at->diffForHumans() }}</small>
    </div>
@endforeach