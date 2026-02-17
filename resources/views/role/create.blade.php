@extends('layouts.header')
@section('title')
College
@endsection
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body p-4">
                        <form action="{{ route($current_menu . '.update', $id) }}" method="POST">
                            @csrf 
                            @method('PUT')
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-3 mt-4">
                                    <div class="form-group">
                                        <input type="hidden" name="role_id" value="{{$id}}">
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3 mt-4">
                                    <div class="form-group">
                                        <input type="hidden" name="college_id" value="33">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-3 mt-4">
                                    <div class="form-group">
                                        <label for="single-select-clear-field" class="form-label">Module</label>
                                        <div>
                                            <select name="module_id" class="form-select single-select-clear-field" data-placeholder="Select Module" required>
                                                <option></option>
                                                @foreach ($module as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="pull-right mt-4">
                                    <a class="btn btn-light" href="{{ route('role.index') }}"> Back</a>
                                    <button style="float:right" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection