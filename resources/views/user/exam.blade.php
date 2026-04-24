@extends('user.layouts.app')

@section('title', 'Sedang Mengerjakan Ujian - UKAICHAMPION APP')

@section('content')
    <livewire:user.exam-play :result_id="$result_id" />
@endsection
