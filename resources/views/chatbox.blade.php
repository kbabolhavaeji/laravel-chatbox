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
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">ChatBox</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12 panel-body" id="panel-body" style="height: 400px; min-height: 400px; overflow: scroll;">
                            <div id="chatbox">
                                @foreach($messages->messages as $message)
                                    <label data-message-id="{{ $message->id }}"><strong>{{ $message->load('user')->user->chat_name }} : </strong></label> <span>{{ $message->message }}</span><br/>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="input-group">
                                <input type="text" name="message" id="message" class="form-control" placeholder="" />
                                <button class="btn btn-primary" id="submit" >Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $('#panel-body').scrollTop($($('#panel-body')).height());

    $("#submit").click(function(e){

        let message = $("#message").val();
        $("#message").val('');
        if(! message.length > 0){
            return;
        }

        $.ajax({
            type:'POST',
            url:"{{ route('send') }}",
            data:{message:message, room_id:{{ $messages->id }}, _token: '{{csrf_token()}}'},
            success:function(data){
                //console.log(data);
            }
        });
    });

</script>

@endsection
