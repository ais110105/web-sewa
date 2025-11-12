@extends('layouts.app')

@section('title', 'Permissions - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Permissions</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('home') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Permissions</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">System Permissions</h4>
                <p class="card-category">View all available permissions in the system</p>
            </div>
            <div class="card-body">
                @foreach($permissions as $group => $perms)
                <div class="mb-4">
                    <h5 class="fw-bold text-primary">{{ $group }} Module</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="40%">Permission Name</th>
                                    <th>Description</th>
                                    <th>Assigned to Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perms as $permission)
                                <tr>
                                    <td><code>{{ $permission->name }}</code></td>
                                    <td>{{ ucwords(str_replace('-', ' ', $permission->name)) }}</td>
                                    <td>
                                        @if($permission->roles->isEmpty())
                                            <span class="badge badge-secondary">No roles</span>
                                        @else
                                            @foreach($permission->roles as $role)
                                                <span class="badge badge-info">{{ ucfirst($role->name) }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
