@extends('pdf.layout')

@section('content')
    <h2>Équipements en Panne</h2>
    <p>Total: {{ $total }} pannes</p>

    <table>
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Référence</th>
                <th>Statut</th>
                <th>Date déclaration</th>
                <th>Agence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pannes as $panne)
            <tr>
                <td>{{ $panne->equipement?->nom ?? 'N/A' }}</td>
                <td>{{ $panne->equipement?->reference ?? 'N/A' }}</td>
                <td>{{ $panne->statut }}</td>
                <td>{{ $panne->date_declaration?->format('d/m/Y') }}</td>
                <td>{{ $panne->equipement?->agenceActuelle?->nom ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
