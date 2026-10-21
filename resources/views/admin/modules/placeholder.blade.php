@extends('layouts.admin')

@section('title', ($title ?? 'Module') . ' - ' . config('app.name'))

@section('admin_content')
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white" id="admin-module-card-{{ Str::slug($title ?? 'page') }}">
    <div class="d-flex justify-content-between align-items-center mb-3" id="admin-module-header-{{ Str::slug($title ?? 'page') }}">
        <h5 class="fw-bold text-dark mb-0" id="admin-module-title-{{ Str::slug($title ?? 'page') }}">
            <i class="fas fa-cubes text-primary me-2"></i> {{ $title ?? 'Admin Module' }}
        </h5>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2" id="admin-module-badge-{{ Str::slug($title ?? 'page') }}">
            Coming soon this module will be available in the next release.
        </span>
    </div>
    <div class="alert alert-light border rounded-3 p-4 text-center my-3" id="admin-module-alert-{{ Str::slug($title ?? 'page') }}">
        <i class="fas fa-tools fa-2x text-muted mb-2 d-block"></i>
        <p class="mb-0 text-secondary small">This section is registered and linked to the active sidebar menu. You can now build the CRUD operations for this screen.</p>
    </div>
</div>
@endsection