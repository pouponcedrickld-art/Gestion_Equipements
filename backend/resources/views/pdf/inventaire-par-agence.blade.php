@extends('pdf.layout')

@section('content')
    <h2>Inventaire - {{ $agence->nom }}</h2>
    <p>Total: {{ $total }} équipements</p>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Statut</th>
                <th>Affecté à</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipements as $equipement)
            <tr>
                <td>{{ $equipement->reference }}</td>
                <td>{{ $equipement->nom }}</td>
                <td>{{ $equipement->categorie?->nom ?? 'N/A' }}</td>
                <td>{{ $equipement->statut_global }}</td>
                <td>{{ $equipement->user?->name ?? 'Non affecté' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
