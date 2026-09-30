@extends('admin.layouts.base')

@section('base_content')

@section('title', 'Créer un Utilisateur')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <a href="{{ route('admin.users.index') }}" class="back-btn">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden" style="border-radius: var(--border-radius);">
        <form action="{{ route('admin.users.store') }}" method="POST" id="createForm">
            @csrf
            <div class="p-6">
                @include('admin.users.form')
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="cancel-btn">
                    Annuler
                </a>
                <button type="button" onclick="confirmCreate()" class="create-btn" style="width: auto; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-save mr-2"></i>Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCreate() {
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const typeCompte = document.getElementById('type_compte_id').value;
    
    if (!name.trim()) {
        alert('Veuillez saisir le nom de l\'utilisateur.');
        return;
    }
    
    if (!email.trim()) {
        alert('Veuillez saisir l\'email de l\'utilisateur.');
        return;
    }
    
    if (!password.trim()) {
        alert('Veuillez saisir le mot de passe.');
        return;
    }
    
    if (!typeCompte) {
        alert('Veuillez sélectionner un type de compte.');
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir créer l'utilisateur "${name}" ?`)) {
        document.getElementById('createForm').submit();
    }
}
</script>
@endsection