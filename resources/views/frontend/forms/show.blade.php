@extends('layouts.app')

@php
    $siteBrandName = config('app.name', 'Shop Now BD');
    $seoTitle = $form->name . ' - ' . $siteBrandName;
    $seoDesc  = !empty($form->description) ? \Illuminate\Support\Str::limit(strip_tags($form->description), 160) : ($form->name . ' on ' . $siteBrandName);
@endphp

@section('title', $seoTitle)
@section('meta_title', $seoTitle)
@section('meta_description', $seoDesc)

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">{{ $form->name }}</li>
                </ol>
            </nav>

            {{-- Dynamic Form Component Load --}}
            @include('frontend.forms.render', ['form' => $form])
            
        </div>
    </div>
</div>
@endsection