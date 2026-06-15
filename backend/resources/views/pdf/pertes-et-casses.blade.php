@extends('pdf.layout')

@section('content')
    <h2>Pertes et Casses</h2>
    <p>Total: {{ $total }} éléments</p>

    <table>
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Type</th>
                <th>Date</th>
                <th>Agence</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pertes as $perte)
            <tr>
                <td>{{ $perte->equipement?->nom ?? 'N/A' }}</td>
                <td>{{ $perte->type }}</td>
                <td>{{ $perte->date?->format('d/m/Y') }}</td>
                <td>{{ $perte->equipement?->agenceActuelle?->nom ?? 'N/A' }}</td>
                <td>{{ Str::limit($perte->description, 50) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
