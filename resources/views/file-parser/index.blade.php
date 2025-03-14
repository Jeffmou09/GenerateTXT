@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">File Parser</div>
                <div class="card-body">
                    @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                    @endif
                    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                        <div class="form-group mb-3">
                            <label for="file_upload">Upload File TXT</label>
                            <input type="file" class="form-control" name="file_upload[]" id="file_upload" accept=".txt" multiple webkitdirectory required>
                            <small class="form-text text-muted">
                                Pilih beberapa file .txt, sistem akan mendeteksi dan memprosesp file .txt.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload dan Proses</button>
                    </form>
                    <hr>
                    <h5>File yang sudah diupload:</h5>
                    @if(count($uploadedFiles) > 0)
                    <div class="list-group mt-3">
                        @foreach($uploadedFiles as $file)
                        <div class="d-flex justify-content-between align-items-center list-group-item">
                            <a href="{{ route('view.file', $file) }}" class="text-decoration-none">
                                {{ $file }}
                            </a>
                            <form action="{{ route('delete.file', $file) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">Belum ada file yang diupload</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
