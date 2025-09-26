-- PostgreSQL schema outline for Jidyan platform
-- Generated as reference for Laravel migrations.

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    locale VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    email_verified_at TIMESTAMPTZ,
    twofa_secret VARCHAR(255),
    remember_token VARCHAR(100),
    last_login_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE profiles_players (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    dob DATE,
    nationality VARCHAR(120),
    city VARCHAR(120),
    country VARCHAR(120),
    height_cm SMALLINT,
    weight_kg SMALLINT,
    position VARCHAR(50),
    preferred_foot VARCHAR(20),
    current_club VARCHAR(120),
    previous_clubs JSONB DEFAULT '[]'::JSONB,
    bio TEXT,
    searchable_text TEXT,
    search_vector TSVECTOR,
    injuries JSONB DEFAULT '[]'::JSONB,
    achievements JSONB DEFAULT '[]'::JSONB,
    visibility VARCHAR(20) DEFAULT 'public',
    availability VARCHAR(30) DEFAULT 'unknown',
    last_active_at TIMESTAMPTZ,
    available_from DATE,
    preferred_roles JSONB DEFAULT '[]'::JSONB,
    verified_identity_at TIMESTAMPTZ,
    verified_academy_at TIMESTAMPTZ,
    view_count INTEGER DEFAULT 0,
    rating SMALLINT DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE player_media (
    id BIGSERIAL PRIMARY KEY,
    player_id BIGINT NOT NULL REFERENCES profiles_players(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL,
    provider VARCHAR(20) NOT NULL DEFAULT 'local',
    original_filename VARCHAR(255),
    path VARCHAR(255) NOT NULL,
    hls_manifest JSONB,
    poster_path VARCHAR(255),
    duration_sec SMALLINT,
    quality_label VARCHAR(20),
    status VARCHAR(20) NOT NULL DEFAULT 'processing',
    processed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE clubs (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(120),
    city VARCHAR(120),
    verified_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE coaches (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    club_id BIGINT REFERENCES clubs(id) ON DELETE SET NULL,
    license_level VARCHAR(120),
    bio TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE agents (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    license_no VARCHAR(120),
    agency_name VARCHAR(255),
    verified_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TYPE link_status AS ENUM ('pending', 'active', 'revoked');
CREATE TABLE player_agent_links (
    id BIGSERIAL PRIMARY KEY,
    player_id BIGINT NOT NULL REFERENCES profiles_players(id) ON DELETE CASCADE,
    agent_id BIGINT NOT NULL REFERENCES agents(id) ON DELETE CASCADE,
    status link_status NOT NULL DEFAULT 'pending',
    approved_at TIMESTAMPTZ,
    revoked_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (player_id, agent_id)
);

CREATE TYPE opportunity_status AS ENUM ('draft', 'published', 'closed');
CREATE TABLE opportunities (
    id BIGSERIAL PRIMARY KEY,
    club_id BIGINT NOT NULL REFERENCES clubs(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements_json JSONB DEFAULT '{}'::JSONB,
    location_city VARCHAR(120),
    location_country VARCHAR(120),
    deadline_at TIMESTAMPTZ,
    status opportunity_status NOT NULL DEFAULT 'published',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TYPE application_status AS ENUM ('received', 'shortlisted', 'invited', 'rejected', 'signed');
CREATE TABLE applications (
    id BIGSERIAL PRIMARY KEY,
    opportunity_id BIGINT NOT NULL REFERENCES opportunities(id) ON DELETE CASCADE,
    player_id BIGINT NOT NULL REFERENCES profiles_players(id) ON DELETE CASCADE,
    media_id BIGINT REFERENCES player_media(id) ON DELETE SET NULL,
    note TEXT,
    status application_status NOT NULL DEFAULT 'received',
    reviewed_by_user_id BIGINT REFERENCES users(id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (opportunity_id, player_id)
);

CREATE TABLE shortlists (
    id BIGSERIAL PRIMARY KEY,
    coach_id BIGINT NOT NULL REFERENCES coaches(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE shortlist_items (
    id BIGSERIAL PRIMARY KEY,
    shortlist_id BIGINT NOT NULL REFERENCES shortlists(id) ON DELETE CASCADE,
    player_id BIGINT NOT NULL REFERENCES profiles_players(id) ON DELETE CASCADE,
    note TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (shortlist_id, player_id)
);

CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    sender_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    body TEXT NOT NULL,
    read_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE verifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(20) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_name VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    reason TEXT,
    reviewed_by BIGINT REFERENCES users(id),
    reviewed_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE view_logs (
    id BIGSERIAL PRIMARY KEY,
    viewer_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    player_id BIGINT NOT NULL REFERENCES profiles_players(id) ON DELETE CASCADE,
    viewed_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE content_reports (
    id BIGSERIAL PRIMARY KEY,
    reportable_type VARCHAR(120) NOT NULL,
    reportable_id BIGINT NOT NULL,
    reporter_user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason VARCHAR(120) NOT NULL,
    description TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    resolved_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    resolved_at TIMESTAMPTZ,
    resolution_notes TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE feature_flags (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(120) UNIQUE NOT NULL,
    enabled BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_players_position ON profiles_players (position);
CREATE INDEX idx_players_city ON profiles_players (city);
CREATE INDEX idx_players_country ON profiles_players (country);
CREATE INDEX idx_players_dob ON profiles_players (dob);
CREATE INDEX idx_players_availability ON profiles_players (availability);
CREATE INDEX idx_players_last_active ON profiles_players (last_active_at);
CREATE INDEX idx_players_search_vector ON profiles_players USING GIN (search_vector);
CREATE INDEX idx_media_status ON player_media (status);
CREATE INDEX idx_applications_status ON applications (status);
CREATE INDEX idx_messages_receiver ON messages (receiver_user_id, read_at);
CREATE INDEX idx_view_logs_player ON view_logs (player_id, viewed_at DESC);
CREATE INDEX idx_content_reports_status ON content_reports (status, created_at DESC);
CREATE INDEX idx_content_reports_reporter ON content_reports (reporter_user_id, status);
