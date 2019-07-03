@extends(layout)

@section(title, Welcome)

@section(main){
    <div>Welcome {{ $id ?? null }}</div>
}
<ul>
@for($i = 1; $i <= 3; $i++){
    <li>{{ $i }}</li>
    
}
</ul>