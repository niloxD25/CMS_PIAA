@extends('admin.layouts.base')

@section('base_content')

@section('title', $typeCompte->type)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $typeCompte->type }}</h1>
            <span class="niveau-badge-large niveau-{{ $typeCompte->niveau_acces }}">
                Niveau {{ $typeCompte->niveau_acces }}
            </span>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.users.types-compte.edit', $typeCompte->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            @if($typeCompte->users_count == 0)
            <button type="button" 
                    onclick="confirmDelete({{ $typeCompte->id }}, '{{ addslashes($typeCompte->type) }}')" 
                    class="edit-btn delete-btn">
                <i class="fas fa-trash mr-2"></i>Supprimer
            </button>
            @endif
            <a href="{{ route('admin.users.types-compte.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Informations principales -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Type de compte</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $typeCompte->type }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Niveau d'accès</label>
                        <p class="mt-1 text-sm text-gray-900">Niveau {{ $typeCompte->niveau_acces }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs associés -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Utilisateurs associés</h2>
                    <span class="text-sm text-gray-500">{{ $typeCompte->users_count }} utilisateur(s)</span>
                </div>
                <div class="p-4">
                    @if($typeCompte->users_count > 0)
                        <div class="space-y-3">
                            @foreach($typeCompte->users as $user)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg" style="border-radius: var(--border-radius);">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">Aucun utilisateur associé à ce type de compte</p>
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
function confirmDelete(typeCompteId, typeCompteType) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type de compte "${typeCompteType}" ?`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/types-compte/${typeCompteId}`;
        form.submit();
    }
}
</script>

<style>
.niveau-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    background: var(--color-warning);
}
</style>
@endsection