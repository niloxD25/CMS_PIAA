@extends('admin.layouts.base')

@section('base_content')
@section('title', $section->titre)

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div class="flex space-x-2">
            <a href="{{ route('admin.projets.apropos-sections.edit', [$projet->id, $section->id]) }}" class="edit-btn">
                <i class="fas fa-edit mr-2"></i>Modifier
            </a>
            <a href="{{ url()->previous() }}" class="back-btn">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
        <div class="text-sm text-gray-600">
            Projet : <span class="font-semibold">{{ $projet->nom }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contenu principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Détails de la section</h2>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Titre -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Titre</label>
                        <p class="mt-1 text-lg text-gray-900">{{ $section->titre }}</p>
                    </div>

                    <!-- Sous-titre -->
                    @if($section->sous_titre)
                    <div>
                        <label class="text-sm font-medium text-gray-500">Sous-titre</label>
                        <p class="mt-1 text-gray-900">{{ $section->sous_titre }}</p>
                    </div>
                    @endif

                    <!-- Description -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <div class="mt-1 prose max-w-none">
                            {!! nl2br(e($section->description)) !!}
                        </div>
                    </div>

                    <!-- Ordre -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Ordre d'affichage</label>
                        <div class="mt-1 flex items-center">
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-sort-numeric-down mr-2"></i>{{ $section->ordre }}
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="pt-3 border-t border-gray-100 space-y-3">
                        @if($section->deleted_at)
                        <div>
                            <label class="text-sm font-medium text-red-500">Supprimée le</label>
                            <p class="mt-1 text-sm text-red-500">
                                {{ \Carbon\Carbon::parse($section->deleted_at)->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div class="space-y-6">
            <!-- Image -->
            @if($section->image)
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Image</h2>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <img src="{{ asset($section->image) }}" 
                             alt="{{ $section->titre }}"
                             class="w-full h-64 object-cover rounded-lg"
                             style="border-radius: var(--border-radius);"
                             onclick="showImageModal('{{ asset($section->image) }}')">
                        <button type="button" 
                                onclick="showImageModal('{{ asset($section->image) }}')"
                                class="absolute top-2 right-2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-opacity"
                                title="Agrandir">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if(!$section->deleted_at)
                    <a href="{{ route('admin.projets.apropos-sections.edit', [$projet->id, $section->id]) }}" 
                       class="edit-btn w-full text-center">
                        <i class="fas fa-edit mr-2"></i>Modifier la section
                    </a>
                    
                    <form action="{{ route('admin.projets.apropos-sections.destroy', [$projet->id, $section->id]) }}" 
                          method="POST" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette section ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="create-btn delete-btn w-full">
                            <i class="fas fa-trash-alt mr-2"></i>Supprimer la section
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('admin.projets.apropos-sections.create', $projet->id) }}" 
                       class="create-btn w-full text-center">
                        <i class="fas fa-plus-circle mr-2"></i>Créer une autre section
                    </a>

                    <a href="{{ route('admin.projets.show', $projet->id) }}" 
                       class="back-btn w-full text-center">
                        <i class="fas fa-project-diagram mr-2"></i>Retour au projet
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour agrandir l'image -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-auto" style="border-radius: var(--border-radius);">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold">Image : {{ $section->titre }}</h3>
            <button type="button" onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4 flex justify-center">
            <img src="{{ asset($section->image) }}" 
                 alt="{{ $section->titre }}"
                 class="max-w-full max-h-[70vh] object-contain rounded"
                 style="border-radius: var(--border-radius);">
        </div>
        <div class="p-4 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">{{ $section->titre }}</p>
            @if($section->sous_titre)
            <p class="text-sm text-gray-500">{{ $section->sous_titre }}</p>
            @endif
        </div>
    </div>
</div>

<script>
function showImageModal(imageSrc) {
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Fermer le modal avec ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Fermer le modal en cliquant à l'extérieur
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target.id === 'imageModal') {
        closeImageModal();
    }
});
</script>

<style>
.prose {
    line-height: 1.75;
}

.prose p {
    margin-bottom: 1em;
}

.prose p:last-child {
    margin-bottom: 0;
}

#imageModal img {
    max-width: 100%;
    height: auto;
}
</style>
@endsection