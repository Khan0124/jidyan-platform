@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('dashboard.club.opportunities.store') }}" class="bg-white p-6 rounded shadow grid gap-4">
    @csrf
    @include('dashboard.club.opportunities.partials.form-fields')
    <button class="bg-blue-600 text-white px-4 py-2 rounded">@lang('Create')</button>
</form>
@endsection
