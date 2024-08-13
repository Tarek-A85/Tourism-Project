<x-mail::message>
<strong>Hello!</strong> <br>
Your booking to {{$place}} is done successfully <br>
details are: <br> <br>
@foreach($details as $detail)
{{$detail}} <br> <br>
@endforeach

You can cancel the reservation or part of it {{$time}} before the reservation

</x-mail::message>
