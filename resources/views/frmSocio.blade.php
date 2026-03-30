@extends('index')
@section('content')
<div id="app">
    <frm-socio
        :rol-usuario="{{ json_encode(DB::table('users')->join('rol', 'rol.id', '=', 'users.id_rol')->select('rol.nombre as nombre_rol')->where('users.id', Auth::user()->id)->get()[0]->nombre_rol) }}">
    </frm-socio>
</div>
@endsection