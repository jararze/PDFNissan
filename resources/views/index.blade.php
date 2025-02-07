<h1>Resumen</h1>
<p>Total de archivos: {{ $totals['total'] }}</p>
<p>Total de archivos procesados: {{ $totals['procesados'] }}</p>
<p>Total de archivos eliminados: {{ $totals['eliminados'] }}</p>

@foreach ($fileCount as $directory => $counts)
    <h2>{{ $directory }}</h2>
    <p>Total de archivos: {{ $counts['total'] }}</p>
    <p>Archivos procesados: {{ $counts['procesados'] }}</p>
    <p>Archivos eliminados: {{ $counts['eliminados'] }}</p>
@endforeach
