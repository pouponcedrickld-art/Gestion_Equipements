@extends('pdf.layout')

@section('content')
    <h2>Maintenances</h2>
    <p>Total: {{ $total }} maintenances</p>

    <table>
        <thead>
            <tr>
                <th>Équipement</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Date prévue</th>
                <th>Technicien</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $maintenance)
            <tr>
                <td>{{ $maintenance->equipement?->nom ?? 'N/A' }}</td>
                <td>{{ $maintenance->type_maintenance }}</td>
                <td>{{ $maintenance->statut }}</td>
                <td>{{ $maintenance->date_prevue?->format('d/m/Y') }}</td>
                <td>{{ $maintenance->technicienUser?->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
