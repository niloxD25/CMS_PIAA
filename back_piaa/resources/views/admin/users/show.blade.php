@extends('admin.layouts.base')

@section('base_content')

@section('title', $user->name)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $user->name }}</h1>
            <div class="flex items-center space-x-2">
                @if($user->email_verified_at)
                    <span class="status-badge active">Email vérifié</span>
                @else
                    <span class="status-badge archived">Email non vérifié</span>
                @endif
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            @if($user->id !== auth()->id())
            <button type="button" 
                    onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                    class="create-btn delete-btn">
                <i class="fas fa-trash mr-2"></i>Supprimer
            </button>
            @endif
            <a href="{{ route('admin.users.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Avatar et informations de base -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Profil</h2>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-user text-white text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
                        <div class="mt-4">
                            <span class="type-compte-badge-large">
                                {{ $user->typeCompte->type ?? 'Aucun type' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du compte -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations du compte</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date de création</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Dernière modification</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email vérifié</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($user->email_verified_at)
                                <span class="text-green-600">Oui ({{ $user->email_verified_at->format('d/m/Y à H:i') }})</span>
                            @else
                                <span class="text-red-600">Non</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails du type de compte -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Permissions et accès</h2>
                </div>
                <div class="p-6">
                    @if($user->typeCompte)
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Type de compte</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $user->typeCompte->type }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Niveau d'accès</label>
                            <p class="mt-1 text-sm text-gray-900">Niveau {{ $user->typeCompte->niveau_acces }}</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-2"></i>
                        <p class="text-gray-500">Aucun type de compte attribué</p>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="edit-btn mt-2 inline-block">
                            <i class="fas fa-edit mr-2"></i>Attribuer un type de compte
                        </a>
                    </div>
                    @endif
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
function confirmDelete(userId, userName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/users/${userId}`;
        form.submit();
    }
}
</script>

<style>
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
}

.status-badge.active {
    background: var(--color-success);
}

.status-badge.archived {
    background: var(--color-warning);
}

.type-compte-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    background: var(--color-primary);
    color: white;
}
</style>
@endsection