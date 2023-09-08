@extends('layouts.app')

@section('content')

    <div class="container">
        <h4>Профили пользователя</h4>
        <div class="block_account">
            <div class="left_block">
                @include('components.accountMenu')
            </div>
            <div class="right_block">
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-2">
                    <a href="{{route('account.profile.create')}}" class="btn btn-primary" type="button">Добавить Адрес</a>
                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Фио</th>
                        <th scope="col">Телефон</th>
                        <th scope="col">Email</th>
                        <th scope="col">Адрес</th>
                        <th scope="col">Действие</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(!empty($profile))
                        @foreach($profile as $key=>$item )
                             @php $key++; @endphp
                            <tr>
                                <th scope="row">{{$key}}</th>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['telephone']}}</td>
                                <td>{{$item['mail']}}</td>
                                <td>{{$item['address']}}</td>
                                <td class="d-flex ">
                                    <div>
                                    <a href="{{route('account.profile.edit',$item['id'])}}">Изменить</a>
                                    <span> /</span>
                                    </div>
                                    <form action="{{route('account.profile.delete',$item['id'])}}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button class="btn-href">удалить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>

            </div>
        </div>
    </div>


@endsection
