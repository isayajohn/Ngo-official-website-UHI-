<ul>@foreach($data['labels'] as $index => $label)<li>{{ $label }}: {{ number_format($data['datasets'][0]['data'][$index]) }}</li>@endforeach</ul>
