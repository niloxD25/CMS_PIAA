@php
    $layout = auth()->user()->type_compte_id == 1 ? 'admin.layouts.admin' : 'admin.layouts.content';
@endphp

@extends($layout)

@section('content')
    @yield('base_content')
@endsection
