@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        {{ __('Edit User') }}
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user->id) }}" method="post">
            @method('PUT')

            @include('users._form', [
            'submitBtnName' => 'Update'
            ])
        </form>
    </div>
</div>
@endsection