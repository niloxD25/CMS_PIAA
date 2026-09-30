-- CREATE USER piaa_user WITH PASSWORD 'piaa.user';

-- ============================================
-- 1. Création de la base de données
-- ============================================
CREATE DATABASE base_piaa
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    LC_COLLATE = 'fr_FR.UTF-8'
    LC_CTYPE = 'fr_FR.UTF-8'
    TEMPLATE = template0;

-- ============================================
-- 2. Connexion à la base
-- ============================================
\c base_piaa;

-- ============================================
-- 3. Création des tables
-- ============================================

-- Comptes & Types
CREATE TABLE types_compte (
    id SERIAL PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    niveau_acces INT NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    mail VARCHAR(150) UNIQUE NOT NULL,
    mdp TEXT NOT NULL,
    type_compte_id INT REFERENCES types_compte(id),
    deleted_at TIMESTAMP
);

-- Projets & Organisations
CREATE TABLE projets (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    devise TEXT,
    acronymes VARCHAR(30), 
    adresse VARCHAR(200),
    email VARCHAR(200),
    telephone TEXT,
    logo VARCHAR(255),
    debut DATE,
    duree_annee INT,
    archived BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP
);

CREATE TABLE projet_apropos_sections (
    id SERIAL PRIMARY KEY,
    projet_id INT NOT NULL REFERENCES projets(id) ON DELETE CASCADE,
    titre VARCHAR(200),
    sous_titre VARCHAR(255),
    description TEXT,
    ordre INT DEFAULT 1,
    image VARCHAR(255),
    deleted_at TIMESTAMP
);

CREATE TABLE projet_en_cours (
    id SERIAL PRIMARY KEY,
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    date_changement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reseaux_sociaux (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) UNIQUE,
    icone VARCHAR(255),
    deleted_at TIMESTAMP
);

CREATE TABLE personnes (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    photo VARCHAR(255),
    poste VARCHAR(250),
    deleted_at TIMESTAMP
);

CREATE TABLE projet_personnes (
    id SERIAL PRIMARY KEY,
    projet_id INTEGER NOT NULL,
    personne_id INTEGER NOT NULL,
    hierarchie INTEGER,
    date_debut_participation DATE,
    date_fin_participation DATE, 
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (personne_id) REFERENCES personnes(id) ON DELETE CASCADE,    
    UNIQUE(projet_id, personne_id)
);

CREATE TABLE projets_reseaux_sociaux (
    id SERIAL PRIMARY KEY,
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    reseau_social_id INT REFERENCES reseaux_sociaux(id) ON DELETE RESTRICT,
    url VARCHAR(500) NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE organisations (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    lien VARCHAR(255),
    deleted_at TIMESTAMP
);

-- ex: 'partenariat financier', 'partie prenante'
CREATE TABLE types_relation (
    id SERIAL PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE organisation_projet (
    id SERIAL PRIMARY KEY,
    organisation_id INT REFERENCES organisations(id) ON DELETE CASCADE,
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    type_relation_id INT REFERENCES types_relation(id) ON DELETE CASCADE
);

-- Contrainte pour garantir l'unicité des combinaisons
CREATE UNIQUE INDEX organisation_projet_unique_triplet
ON organisation_projet (organisation_id, projet_id, type_relation_id);

-- Événements
CREATE TABLE etats_evenement (
    id SERIAL PRIMARY KEY,
    etat VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE evenements (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date_debut DATE,
    date_fin DATE,
    image VARCHAR(255),
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    niveau_acces INT,
    est_actualite BOOLEAN DEFAULT FALSE,
    archived BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP
);

CREATE TABLE changement_etat_evenement (
    id SERIAL PRIMARY KEY,
    evenement_id INT REFERENCES evenements(id) ON DELETE CASCADE,
    etat_evenement_id INT REFERENCES etats_evenement(id),
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sections (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200),
    description TEXT,
    image VARCHAR(255),
    evenement_id INT REFERENCES evenements(id) ON DELETE CASCADE,
    deleted_at TIMESTAMP
);

-- Témoignages & Réalisations
CREATE TABLE temoignages (
    id SERIAL PRIMARY KEY,
    temoin VARCHAR(150),
    residence VARCHAR(200),
    contenu TEXT,
    image VARCHAR(255),
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    est_important BOOLEAN DEFAULT FALSE,
    archived BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP
);

CREATE TABLE realisations (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200),
    beneficiaires TEXT,
    description TEXT,
    impacts_potentiel TEXT,
    date_realisation DATE,
    image VARCHAR(255),
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    niveau_acces INT,
    archived BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP
);

-- Messages & Annonces
CREATE TABLE types_message (
    id SERIAL PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    mail VARCHAR(150),
    telephone TEXT,
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    type_message_id INT REFERENCES types_message(id),
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    deleted_at TIMESTAMP
);

-- Création des indexes
CREATE INDEX idx_messages_is_read ON messages(is_read);
CREATE INDEX idx_messages_type_id ON messages(type_message_id);
CREATE INDEX idx_messages_date_envoi ON messages(date_envoi);


CREATE TABLE types_annonce (
    id SERIAL PRIMARY KEY,
    type VARCHAR(100) NOT NULL,
    deleted_at TIMESTAMP
);

CREATE TABLE annonces (
    id SERIAL PRIMARY KEY,
    type_annonce_id INT NOT NULL REFERENCES types_annonce(id),
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    contenu TEXT,
    a_defiler BOOLEAN DEFAULT FALSE,
    date_affichage TIMESTAMP,
    nbr_jours_affichage INT,
    deleted_at TIMESTAMP
);

-- ex: 'projet', 'evenement', 'realisation'
CREATE TABLE entites_fichier (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    deleted_at TIMESTAMP
);

-- Table des fichiers
CREATE TABLE fichiers (
    id SERIAL PRIMARY KEY,
    titre VARCHAR(200),
    description TEXT,
    type_fichier VARCHAR(50),
    chemin VARCHAR(255) NOT NULL,
    dossier VARCHAR(255),
    date_upload DATE DEFAULT CURRENT_DATE,
    entite_type_id INT NOT NULL REFERENCES entites_fichier(id) ON DELETE CASCADE,
    entite_id INT,
    niveau_acces INT,
    projet_id INT REFERENCES projets(id) ON DELETE CASCADE,
    archived BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP
);

INSERT INTO entites_fichier (nom) VALUES 
('Documentation'),
('EvÚnement'),
('RÚalisation'),
('Travaux'),
('Interview'),
('Actions de proximitÚ');

INSERT INTO types_relation (type) VALUES 
('partie prenante'),
('partenariat financier');

INSERT INTO etats_evenement (etat) VALUES 
('TerminÚ'),
('A venir'),
('En cours'),
('AnnulÚ');
