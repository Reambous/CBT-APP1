@extends('user.layouts.app')

@section('title', 'Edit Profil - UKAICHAMPION APP')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
    </div>

    @livewire('user.profile-edit')
@endsection
