@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('dashboard.club.opportunities.update', $opportunity) }}" class="bg-white p-6 rounded shadow grid gap-4">
    @csrf
    @method('PUT')
    @include('dashboard.club.opportunities.partials.form-fields', ['opportunity' => $opportunity])
    <button class="bg-blue-600 text-white px-4 py-2 rounded">@lang('Update')</button>
</form>
@endsection
