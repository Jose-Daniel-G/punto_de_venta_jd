<div class="row">
    <div class="col-6">
        <label class="badge bg-primary">Total Registros CSV: {{ $csvCount }}</label>
        @if (!empty($records))
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Registro CSV/XLSL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $row)
                        <tr>
                            @foreach (array_slice($row, 0, 2) as $value)
                                {{-- Tomar solo las dos primeras columnas --}}
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay datos en el archivo CSV/XLSL.</p>
        @endif
    </div>
    <div class="col-6">
        <label class="badge bg-success">Total Archivos PDF: {{ $pdfCount }}</label>
        @if (!empty($pdfFiles))
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Archivos PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pdfFiles as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                                <td>{{ $row }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay archivos PDF.</p>
        @endif
    </div>
</div>