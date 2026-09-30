@extends('admin.layouts.base')

@section('base_content')
@section('title', 'Gestion des Réseaux Sociaux')

<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.reseaux-sociaux.create') }}" class="add-btn">
            <i class="fas fa-plus mr-2"></i>Nouveau Réseau Social
        </a>
    </div>

    <!-- Tableau des réseaux sociaux -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="projets-table">
                <thead>
                    <tr>
                        <th class="table-header">Icône</th>
                        <th class="table-header">Nom</th>
                        <th class="table-header">Nombre de projets</th>
                        <th class="table-header actions-header">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reseauxSociaux as $reseau)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div class="flex justify-center">
                                <div class="social-icon-admin">
                                    <i class="{{ $reseau->font_awesome_icon }} text-xl"></i>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="text-center font-medium">{{ $reseau->nom }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="text-center">
                                <span class="badge-count">{{ $reseau->projets->count() }}</span>
                            </div>
                        </td>
                        <td class="table-cell actions-cell">
                            <div class="actions-container justify-center">
                                <a href="{{ route('admin.reseaux-sociaux.edit', $reseau->id) }}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmDelete({{ $reseau->id }}, '{{ addslashes($reseau->nom) }}')" 
                                        class="action-btn delete-btn"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="table-row">
                        <td colspan="4" class="table-cell empty-cell">
                            <div class="empty-state">
                                <i class="fas fa-share-alt text-gray-400 text-4xl mb-3"></i>
                                <p>Aucun réseau social trouvé</p>
                                <a href="{{ route('admin.reseaux-sociaux.create') }}" class="add-btn empty-btn">
                                    <i class="fas fa-plus mr-2"></i>Créer le premier réseau social
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(reseauId, reseauNom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le réseau social "${reseauNom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/reseaux-sociaux/${reseauId}`;
        form.submit();
    }
}
</script>

<style>
.social-icon-admin {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
}

.badge-count {
    background: var(--color-primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}
</style>
@endsection