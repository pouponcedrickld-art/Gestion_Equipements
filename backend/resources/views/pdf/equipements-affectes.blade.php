@extends('pdf.layout')

@section('content')
    <h2>Équipements Affectés</h2>
    <p>Total: {{ $total }} équipements</p>

    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Utilisateur</th>
                <th>Agence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($equipements as $equipement)
            <tr>
                <td>{{ $equipement->reference }}</td>
                <td>{{ $equipement->nom }}</td>
                <td>{{ $equipement->categorie?->nom ?? 'N/A' }}</td>
                <td>{{ $equipement->user?->name ?? 'N/A' }}</td>
                <td>{{ $equipement->agenceActuelle?->nom ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
