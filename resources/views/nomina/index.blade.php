@extends('layouts.app')

@section('title', 'Nómina')
@section('page-title', 'NÓMINA')

@section('content')

@include('nomina.partials.index_content')

<script>
    flatpickr('#periodo-liquidacion', {
        locale: 'es',
        mode: 'range',
        dateFormat: 'd/m/Y',
        placeholder: 'Seleccionar rango de fechas'
    });
</script>

@endsection
