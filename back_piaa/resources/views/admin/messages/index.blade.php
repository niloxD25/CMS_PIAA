@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Gestion des Messages')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.types-messages.index') }}" class="add-btn">
                <i class="fas fa-tags mr-2"></i>Types de Messages
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6" style="border-radius: var(--border-radius);">
        <div class="p-6">
            <form method="GET" class="space-y-4">
                <!-- Barre de recherche -->
                <div class="mb-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="relative">
                        <input type="text" 
                            id="search" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Rechercher par nom, email, téléphone ou contenu..."
                            class="form-input pl-10 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('admin.messages.index', array_merge(request()->except(['search', 'page']))) }}" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-gray-700"
                            title="Effacer la recherche">
                                <i class="fas fa-times text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="type_message_id" class="form-label">Type de message</label>
                        <select id="type_message_id" name="type_message_id" class="form-input">
                            <option value="">Tous les types</option>
                            @foreach($typesMessage as $type)
                                <option value="{{ $type->id }}" {{ request('type_message_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="is_read" class="form-label">Statut de lecture</label>
                        <select id="is_read" name="is_read" class="form-input">
                            <option value="">Tous les messages</option>
                            <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Non lus ({{ $unreadCount }})</option>
                            <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Lus ({{ $readCount }})</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort_by" class="form-label">Trier par</label>
                        <select id="sort_by" name="sort_by" class="form-input">
                            <option value="date_envoi" {{ request('sort_by', 'date_envoi') == 'date_envoi' ? 'selected' : '' }}>Date d'envoi</option>
                            <option value="nom" {{ request('sort_by') == 'nom' ? 'selected' : '' }}>Nom</option>
                            <option value="mail" {{ request('sort_by') == 'mail' ? 'selected' : '' }}>Email</option>
                            <option value="is_read" {{ request('sort_by') == 'is_read' ? 'selected' : '' }}>Statut de lecture</option>
                            <option value="type_message_id" {{ request('sort_by') == 'type_message_id' ? 'selected' : '' }}>Type de message</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex space-x-3 justify-between items-center">
                    <div class="flex space-x-3">
                        <button type="submit" class="create-btn">
                            <i class="fas fa-filter mr-2"></i>Appliquer les filtres
                        </button>
                        <a href="{{ route('admin.messages.index') }}" class="cancel-btn">
                            <i class="fas fa-times mr-2"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des messages -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="messages-table">
                <thead>
                    <tr>
                        <th class="table-header">Statut</th>
                        <th class="table-header sortable {{ $sortBy == 'nom' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nom', 'sort_order' => $sortBy == 'nom' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Expéditeur
                                @if($sortBy == 'nom')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header sortable {{ $sortBy == 'mail' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'mail', 'sort_order' => $sortBy == 'mail' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Email/Téléphone
                                @if($sortBy == 'mail')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header">Message</th>
                        <th class="table-header">Type</th>
                        <th class="table-header sortable {{ $sortBy == 'date_envoi' ? 'sorting-' . $sortOrder : '' }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_envoi', 'sort_order' => $sortBy == 'date_envoi' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                Date d'envoi
                                @if($sortBy == 'date_envoi')
                                    <i class="fas fa-sort-{{ $sortOrder == 'asc' ? 'up' : 'down' }} sort-icon"></i>
                                @else
                                    <i class="fas fa-sort sort-icon"></i>
                                @endif
                            </a>
                        </th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                    <tr class="table-row {{ $message->is_read ? 'bg-gray-50' : 'bg-blue-50' }}">
                        <td class="table-cell">
                            <div class="flex justify-center">
                                @if($message->is_read)
                                    <span class="read-status read" title="Lu le {{ $message->read_at ? $message->read_at->format('d/m/Y H:i') : 'Date inconnue' }}">
                                        <i class="fas fa-envelope-open"></i>
                                        <span class="tooltip-text">Lu le {{ $message->read_at ? $message->read_at->format('d/m/Y H:i') : 'Date inconnue' }}</span>
                                    </span>
                                @else
                                    <span class="read-status unread" title="Non lu - Nouveau message">
                                        <i class="fas fa-envelope"></i>
                                        <span class="tooltip-text">Non lu - Nouveau message</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="sender-name {{ !$message->is_read ? 'font-bold' : '' }}">{{ $message->nom }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="contact-info">
                                @if($message->mail)
                                    <div class="email"><i class="fas fa-envelope mr-1"></i>{{ $message->mail }}</div>
                                @endif
                                @if($message->telephone)
                                    <div class="phone"><i class="fas fa-phone mr-1"></i>{{ $message->telephone }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="message-preview {{ !$message->is_read ? 'font-medium' : '' }}">
                                {{ Str::limit($message->message, 80) }}
                            </div>
                        </td>
                        <td class="table-cell">
                            <form action="{{ route('admin.messages.update-type', $message->id) }}" method="POST" class="type-form">
                                @csrf
                                @method('PUT')
                                <select name="type_message_id" class="type-select type-{{ $message->type_message_id ?? 3 }}" onchange="this.form.submit()">
                                    @foreach($typesMessage as $type)
                                        <option value="{{ $type->id }}" 
                                                {{ ($message->type_message_id ?? 3) == $type->id ? 'selected' : '' }}
                                                data-type="{{ $type->id }}">
                                            {{ $type->type }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="table-cell">
                            @if($message->date_envoi)
                                <span class="date-badge">
                                    {{ \Carbon\Carbon::parse($message->date_envoi)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="no-date">Non définie</span>
                            @endif
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container">
                                <a href="{{ route('admin.messages.show', $message->id) }}" 
                                   class="action-btn view-btn" 
                                   title="Voir le message"
                                   onclick="markAsRead({{ $message->id }})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $message->id }}, '{{ addslashes($message->nom) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="7" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-envelope"></i>
                                <p>Aucun message trouvé</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="pagination-controls">
                <!-- Sélection du nombre d'éléments par page -->
                <div class="per-page-selector">
                    <label for="per_page" class="per-page-label">Afficher :</label>
                    <select id="per_page" name="per_page" onchange="updatePerPage(this.value)" class="styled-select">
                        <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span class="per-page-text">éléments par page</span>
                </div>

                <!-- Informations de pagination -->
                <div class="pagination-info">
                    Affichage de <strong>{{ $messages->firstItem() ?? 0 }}</strong> à <strong>{{ $messages->lastItem() ?? 0 }}</strong> sur <strong>{{ $messages->total() }}</strong> messages
                </div>

                <!-- Liens de pagination -->
                <div class="pagination-links">
                    {{ $messages->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
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
    if (confirm(`Êtes-vous sûr de vouloir supprimer le message de "${messageNom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/messages/${messageId}`;
        form.submit();
    }
}

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Mise à jour dynamique des couleurs des selects
document.addEventListener('DOMContentLoaded', function() {
    const typeSelects = document.querySelectorAll('.type-select');
    
    typeSelects.forEach(select => {
        updateSelectColor(select);
        
        select.addEventListener('change', function() {
            this.className = 'type-select type-' + this.value;
            updateSelectColor(this);
        });
    });
    
    function updateSelectColor(select) {
        const value = select.value;
        switch(value) {
            case '1':
                select.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
                select.style.borderColor = 'rgb(239, 68, 68)';
                select.style.color = 'rgb(239, 68, 68)';
                break;
            case '2':
                select.style.backgroundColor = 'rgba(107, 114, 128, 0.1)';
                select.style.borderColor = 'rgb(107, 114, 128)';
                select.style.color = 'rgb(107, 114, 128)';
                break;
            case '3':
            default:
                select.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                select.style.borderColor = 'rgb(59, 130, 246)';
                select.style.color = 'rgb(59, 130, 246)';
                break;
        }
    }
});
</script>

<style>
.read-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
}

.read-status i {
    font-size: 1.1rem;
}

.read-status.read {
    background-color: rgba(34, 197, 94, 0.1);
    color: rgb(34, 197, 94);
}

.read-status.unread {
    background-color: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Tooltip pour le statut */
.read-status .tooltip-text {
    visibility: hidden;
    width: 180px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1000;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.8rem;
    white-space: nowrap;
}

.read-status .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}

.read-status:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}

.mark-read-btn {
    background-color: rgba(34, 197, 94, 0.1) !important;
    color: rgb(34, 197, 94) !important;
    border-color: rgba(34, 197, 94, 0.3) !important;
}

.mark-read-btn:hover {
    background-color: rgba(34, 197, 94, 0.2) !important;
}

.mark-unread-btn {
    background-color: rgba(234, 179, 8, 0.1) !important;
    color: rgb(234, 179, 8) !important;
    border-color: rgba(234, 179, 8, 0.3) !important;
}

.mark-unread-btn:hover {
    background-color: rgba(234, 179, 8, 0.2) !important;
}

.bg-blue-50 {
    background-color: rgba(59, 130, 246, 0.05) !important;
    border-left: 3px solid rgb(59, 130, 246);
}

.bg-blue-50:hover {
    background-color: rgba(59, 130, 246, 0.1) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .read-status {
        width: 28px;
        height: 28px;
    }
    
    .read-status i {
        font-size: 0.9rem;
    }
    
    .read-status .tooltip-text {
        width: 140px;
        font-size: 0.7rem;
    }
    
    .actions-container {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 0.8rem;
    }
}
</style>
@endsection