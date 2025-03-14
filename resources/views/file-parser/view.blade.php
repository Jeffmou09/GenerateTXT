@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>File: {{ $filename }}</span>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                @if(!empty($parsedData['D01']['data']))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ $parsedData['formName'] ?? 'Data Tidak Diketahui' }}</h5> 
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        @foreach($parsedData['D01']['data'] as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No data found or unable to parse the file.
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection