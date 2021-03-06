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
                                <input type="text" name="searchbox" id="searchbox" class="form-control" placeholder="Search ..."/>
                            </div>
                            <div class="col-sm-12" id="search_results" style="border:1px solid #ced4da; margin-top: 2px; border-radius: 5px; z-index: 100; position: absolute; width: 90%; background: #ffffff; visibility: hidden;"></div>
                        </div>
                        <div class="col-sm-12">
                            <ul style="margin-top: 20px; list-style-type: none;">
                                <li><b>Public Rooms</b></li>
                                <li>
                                    @if(isset($generalRooms))
                                        <ul>
                                            @foreach($generalRooms as $room)
                                                <li><a href="{{ route('room.public', ['room' => $room->code]) }}">{{ $room->name }}</a></li>
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
                <div class="card-header">dashboard</div>
                <div class="card-body" style="min-height: 400px;">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
    <script type="text/javascript">
        $('#searchbox').keyup(function (){
            $('#search_results').empty();
            let query = $('#searchbox').val();
            if(query.length > 0){
                $.ajax({
                    type:'POST',
                    url:"{{ route('search') }}",
                    data:{query:query, _token: '{{ csrf_token() }}'},
                    success:function(data){
                        if(data.length > 0){
                            $.each(data, function (index, value){
                                $('#search_results').append('<p style="margin:5px 0px 5px 0px;">[ '+value.type+' ] <strong><a href="'+value.link+'">'+value.name+'</a></strong></p>');
                            });
                        }else{
                            $('#search_results').append('<p style="margin:5px 0px 5px 0px;"><strong> No result has found </strong></p>');
                        }
                    }
                });
                $('#search_results').css('visibility', 'visible');
            }else{
                $('#search_results').css('visibility', 'hidden');
            }
        });
    </script>
@endsection
