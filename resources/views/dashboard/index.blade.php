@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @if($role === 'super_admin')
        @include('dashboard.partials.super_admin_widgets')
    @elseif($role === 'hr_admin')
        @include('dashboard.partials.hr_widgets')
    @elseif($role === 'manager')
        @include('dashboard.partials.manager_widgets')
    @else
        @include('dashboard.partials.employee_widgets')
    @endif
@endsection
