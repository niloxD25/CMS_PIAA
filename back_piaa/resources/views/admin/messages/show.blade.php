@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Message de ' . $message->nom)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            @if($message->date_envoi)
                <span class="date-header-badge">
                    {{ \Carbon\Carbon::parse($message->date_envoi)->format('d/m/Y à H:i') }}
                </span>
            @endif
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.messages.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations du message -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Contenu du message</h2>
                </div>
                <div class="p-6">
                    <div class="message-content">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations expéditeur et actions -->
        <div class="space-y-6">
            <!-- Informations expéditeur -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Expéditeur</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nom</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $message->nom }}</p>
                    </div>
                    @if($message->mail)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="mailto:{{ $message->mail }}" class="text-blue-600 hover:text-blue-900">
                                {{ $message->mail }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($message->telephone)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Téléphone</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <a href="tel:{{ $message->telephone }}" class="text-blue-600 hover:text-blue-900">
                                {{ $message->telephone }}
                            </a>
                        </p>
                    </div>
                    @endif
                    @if($message->types_message)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Type de message</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="type-badge">{{ $message->types_message->type }}</span>
                        </p>
                    </div>
                    @endif
                    @if($message->date_envoi)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date d'envoi</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($message->date_envoi)->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    @endif
                    @if($message->read_at)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date de lecture</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($message->read_at)->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <button type="button" 
                            onclick="confirmDelete({{ $message->id }}, '{{ addslashes($message->nom) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer le message
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(messageId, messageNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le message de "${messageNom}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/messages/${messageId}`;
        form.submit();
    }
}
</script>

<style>
.date-header-badge {
    background: rgba(168, 85, 247, 0.1);
    color: rgb(168, 85, 247);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(168, 85, 247, 0.3);
}

.type-badge {
    background: rgba(59, 130, 246, 0.1);
    color: var(--color-success);
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .d-flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .flex.items-center.space-x-4 {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .date-header-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
    }
    
    .flex.space-x-2 {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
@endsection