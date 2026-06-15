@extends('pdf.layout')

@section('content')
    <h2>Audit Complet{{ $agence ? ' - ' . $agence->nom : '' }}</h2>

    <table>
        <tr>
            <th style="width: 50%; background-color: #3b82f6; color: white; padding: 15px;">Indicateur</th>
            <th style="width: 50%; background-color: #3b82f6; color: white; padding: 15px;">Valeur</th>
        </tr>
        <tr>
            <td>Total équipements</td>
            <td>{{ $inventaire_total }}</td>
        </tr>
        <tr>
            <td>Équipements affectés</td>
            <td>{{ $equipements_affectes }}</td>
        </tr>
        <tr>
            <td>Total pannes</td>
            <td>{{ $pannes_total }}</td>
        </tr>
        <tr>
            <td>Pannes résolues</td>
            <td>{{ $pannes_resolues }}</td>
        </tr>
        <tr>
            <td>Maintenances</td>
            <td>{{ $maintenances_total }}</td>
        </tr>
        <tr>
            <td>Pertes et cassés</td>
            <td>{{ $pertes_total }}</td>
        </tr>
    </table>
@endsection
