@extends('layouts.chat')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Your chat name : {{ \Illuminate\Support\Facades\Auth::user()->chat_name }}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="input-group">
                                <input type="text" name="message" class="form-control" placeholder="Search ..."/>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <ul style="margin-top: 20px; list-style-type: none;">
                                <li><b>Public Rooms</b></li>
                                <li>
                                    @if(isset($rooms))
                                        <ul>
                                            @foreach($rooms as $room)
                                                <li><a href="{{ route('chat.room', ['room'=>$room->id]) }}">{{ $room->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-12">
                            <ul style="margin-top: 20px; list-style-type: none;">
                                <li><b>Users</b></li>
                                <li>
                                    <ul>
                                        <li><a href="{{ route('private.room', ['user'=> 2 ]) }}"> user 2 </a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">dashboard</div>
                <div class="card-body">
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $("#submit").click(function(e){

        let message = $("#message").val();
        $("#message").val('');
        if(! message.length > 0){
            return;
        }

        $.ajax({
            type:'POST',
            url:"{{ route('send') }}",
            data:{message:message, room_id:1, _token: '{{csrf_token()}}'},
            success:function(data){
                console.log(data);
            }
        });
    });

</script>

@endsection
