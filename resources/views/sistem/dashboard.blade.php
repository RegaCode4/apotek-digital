@extends('sistem.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="mt-2 text-zinc-600">
            Anda login sebagai
            <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-sm font-medium capitalize text-zinc-800">
                {{ auth()->user()->role }}
            </span>
        </p>
        <p class="mt-4 text-sm text-zinc-500">Halaman dashboard sistem internal apotek.</p>
    </div>
@endsection
