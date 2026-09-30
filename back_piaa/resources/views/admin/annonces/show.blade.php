@extends('admin.layouts.base')

@section('base_content')

@section('title', $annonce->titre)

@php
    $now = \Carbon\Carbon::now();
    $dateFin = $annonce->date_affichage ? $annonce->date_affichage->copy()->addDays($annonce->nbr_jours_affichage ?? 0) : null;
    $isActive = $annonce->date_affichage && $annonce->date_affichage <= $now && $dateFin && $dateFin >= $now;
    $isFuture = $annonce->date_affichage && $annonce->date_affichage > $now;
    $isExpired = $annonce->date_affichage && $dateFin && $dateFin < $now;
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex items-center space-x-4">
            <h1 class="admin-title">{{ $annonce->titre }}</h1>
            @if($isActive)
            <div class="status-badge-large status-active">
                <i class="fas fa-eye mr-2"></i>Active
            </div>
            @elseif($isFuture)
            <div class="status-badge-large status-future">
                <i class="fas fa-clock mr-2"></i>Programmée
            </div>
            @elseif($isExpired)
            <div class="status-badge-large status-expired">
                <i class="fas fa-ban mr-2"></i>Expirée
            </div>
            @else
            <div class="status-badge-large status-inactive">
                <i class="fas fa-pause mr-2"></i>Inactive
            </div>
            @endif
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.annonces.edit', $annonce->id) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ route('admin.annonces.index') }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Informations de l'annonce</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Titre</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $annonce->titre }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Contenu</label>
                        <div class="mt-1 text-sm text-gray-900 prose max-w-none">
                            {!! nl2br(e($annonce->contenu)) ?? 'Aucun contenu' !!}
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date d'affichage</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $annonce->date_affichage ? $annonce->date_affichage->format('d/m/Y à H:i') : 'Non définie' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Nombre de jours d'affichage</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $annonce->nbr_jours_affichage ? $annonce->nbr_jours_affichage . ' jour' . ($annonce->nbr_jours_affichage > 1 ? 's' : '') : 'Illimitée' }}</p>
                    </div>
                    @if($annonce->date_affichage && $dateFin)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Période d'affichage</label>
                        <p class="mt-1 text-sm text-gray-900">
                            Du {{ $annonce->date_affichage->format('d/m/Y à H:i') }}<br>
                            Au {{ $dateFin->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statut et actions -->
        <div class="space-y-6">
            <!-- Statut détaillé -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Statut</h2>
                </div>
                <div class="p-6">
                    <div class="status-indicator {{ $isActive ? 'status-actuel' : ($isFuture ? 'status-future' : ($isExpired ? 'status-expired' : 'status-normal')) }}">
                        <div class="status-icon">
                            @if($isActive)
                            <i class="fas fa-eye"></i>
                            @elseif($isFuture)
                            <i class="fas fa-clock"></i>
                            @elseif($isExpired)
                            <i class="fas fa-ban"></i>
                            @else
                            <i class="fas fa-pause"></i>
                            @endif
                        </div>
                        <div class="status-content">
                            <p class="status-title">
                                @if($isActive)
                                Annonce active
                                @elseif($isFuture)
                                Annonce programmée
                                @elseif($isExpired)
                                Annonce expirée
                                @else
                                Annonce inactive
                                @endif
                            </p>
                            <p class="status-description">
                                @if($isActive)
                                Cette annonce est actuellement visible par les utilisateurs.
                                @elseif($isFuture)
                                Cette annonce sera affichée automatiquement à la date programmée.
                                @elseif($isExpired)
                                Cette annonce n'est plus affichée car la période d'affichage est terminée.
                                @else
                                Cette annonce n'est pas configurée pour être affichée automatiquement.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <button type="button" 
                            onclick="confirmDelete({{ $annonce->id }}, '{{ addslashes($annonce->titre) }}')" 
                            class="create-btn delete-btn w-full">
                        <i class="fas fa-trash mr-2"></i>Supprimer l'annonce
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
function confirmDelete(annonceId, annonceTitre) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer l'annonce "${annonceTitre}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/annonces/${annonceId}`;
        form.submit();
    }
}
</script>

<style>
.status-badge-large {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
}

.status-badge-large.status-active {
    background: rgba(16, 185, 129, 0.1);
    color: rgb(16, 185, 129);
}

.status-badge-large.status-future {
    background: rgba(59, 130, 246, 0.1);
    color: rgb(59, 130, 246);
}

.status-badge-large.status-expired {
    background: rgba(239, 68, 68, 0.1);
    color: rgb(239, 68, 68);
}

.status-badge-large.status-inactive {
    background: rgba(107, 114, 128, 0.1);
    color: rgb(107, 114, 128);
}

.status-indicator {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    border-radius: var(--border-radius);
}

.status-actuel {
    background: rgba(16, 185, 129, 0.1);
}

.status-future {
    background: rgba(59, 130, 246, 0.1);
}

.status-expired {
    background: rgba(239, 68, 68, 0.1);
}

.status-normal {
    background: rgba(107, 114, 128, 0.1);
}

.status-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.status-actuel .status-icon {
    background: rgba(16, 185, 129, 0.2);
    color: rgb(16, 185, 129);
}

.status-future .status-icon {
    background: rgba(59, 130, 246, 0.2);
    color: rgb(59, 130, 246);
}

.status-expired .status-icon {
    background: rgba(239, 68, 68, 0.2);
    color: rgb(239, 68, 68);
}

.status-normal .status-icon {
    background: rgba(107, 114, 128, 0.2);
    color: rgb(107, 114, 128);
}

.status-content {
    flex: 1;
}

.status-title {
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: var(--brun-fonce);
}

.status-description {
    margin: 0;
    font-size: 0.85rem;
    color: var(--brun-clair);
    line-height: 1.4;
}

.prose {
    max-width: none;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
    .d-flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .flex.space-x-2 {
        width: 100%;
        justify-content: flex-end;
    }
    
    .status-badge-large {
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem;
    }
    
    .status-indicator {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
}
</style>
@endsection